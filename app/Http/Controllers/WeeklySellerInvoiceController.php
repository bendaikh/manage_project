<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklySellerInvoice;
use App\Models\Order;
use App\Models\SellerInvoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WeeklySellerInvoiceController extends Controller
{
    /**
     * List weekly seller invoices (optionally filtered by seller role)
     */
    public function index(Request $request)
    {
        // Check if user has permission to view seller invoices
        if (!Auth::user()->hasPermission('view_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to view seller invoices.'], 403);
        }

        $perPage = $request->input('per_page', 10);
        $sellerFilter = $request->input('seller');

        $query = WeeklySellerInvoice::with('approver')->orderByDesc('week_start_date');

        // If the authenticated user has role 'seller', limit to their approved invoices only
        if (Auth::check() && Auth::user()->hasRole('seller')) {
            $query->where('seller', Auth::user()->name)
                  ->where('status', 'approved');
        }

        // Apply seller filter if provided
        if ($sellerFilter) {
            $query->where('seller', $sellerFilter);
        }

        // Get paginated results and append week_period attribute
        $invoices = $query->paginate($perPage);
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->append('week_period');
            return $invoice;
        });
        
        return response()->json($invoices);
    }

    /**
     * Get list of unique sellers for filter dropdown
     */
    public function getSellers()
    {
        $sellers = WeeklySellerInvoice::distinct()
            ->pluck('seller')
            ->filter()
            ->sort()
            ->values();

        return response()->json($sellers);
    }

    /**
     * Generate weekly invoices for all sellers
     */
    public function generateWeeklyInvoices(Request $request)
    {
        // Check if user has permission to generate seller invoices
        if (!Auth::user()->hasPermission('generate_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to generate seller invoices.'], 403);
        }

        $weekStart = $request->input('week_start');
        $weekEnd = $request->input('week_end');

        if (!$weekStart || !$weekEnd) {
            return response()->json(['error' => 'Week start and end dates are required'], 400);
        }

        try {
            // Debug: Log the date range being searched
            \Log::info('Weekly invoice generation - Date range: ' . $weekStart . ' to ' . $weekEnd);
            
            // Debug: Log all daily invoices
            $allDailyInvoices = \App\Models\SellerInvoice::all();
            \Log::info('All daily invoices: ' . $allDailyInvoices->count());
            foreach ($allDailyInvoices as $inv) {
                \Log::info('Daily invoice: ' . $inv->seller . ' - ' . $inv->invoice_date . ' - ' . $inv->order_count . ' orders');
            }
            
            // Get all unique sellers who have daily invoices in this week period
            $sellers = \App\Models\SellerInvoice::whereBetween('invoice_date', [$weekStart, $weekEnd])
                ->distinct()
                ->pluck('seller')
                ->filter();

            \Log::info('Found sellers with daily invoices in date range: ' . $sellers->count());
            \Log::info('Sellers: ' . $sellers->implode(', '));

            $generatedInvoices = [];

            foreach ($sellers as $sellerName) {
                \Log::info('Processing seller: ' . $sellerName);
                
                // Check if weekly invoice already exists for this seller and week
                $existingInvoice = WeeklySellerInvoice::where('seller', $sellerName)
                    ->where('week_start_date', $weekStart)
                    ->first();

                if ($existingInvoice) {
                    \Log::info('Weekly invoice already exists for seller: ' . $sellerName . ' and week: ' . $weekStart);
                    continue; // Skip if already exists
                }
                
                \Log::info('No existing weekly invoice found, proceeding to create for seller: ' . $sellerName);

                // Get daily invoices for this seller in this week period
                $dailyInvoices = \App\Models\SellerInvoice::where('seller', $sellerName)
                    ->whereBetween('invoice_date', [$weekStart, $weekEnd])
                    ->get();

                \Log::info('Daily invoices found for seller ' . $sellerName . ': ' . $dailyInvoices->count());
                foreach ($dailyInvoices as $dailyInv) {
                    \Log::info('Daily invoice: ' . $dailyInv->invoice_date . ' - ' . $dailyInv->order_count . ' orders - ' . $dailyInv->total_amount . ' FCFA');
                }

                if ($dailyInvoices->isEmpty()) {
                    \Log::info('No daily invoices found for seller: ' . $sellerName . ' in date range');
                    continue;
                }

                // Calculate totals from daily invoices
                $totalOrders = $dailyInvoices->sum('order_count');
                $totalAmount = $dailyInvoices->sum('total_amount');
                
                \Log::info('Calculated totals for seller ' . $sellerName . ': ' . $totalOrders . ' orders, ' . $totalAmount . ' FCFA');

                // Create weekly invoice record
                $weeklyInvoice = WeeklySellerInvoice::create([
                    'seller' => $sellerName,
                    'week_start_date' => $weekStart,
                    'week_end_date' => $weekEnd,
                    'order_count' => $totalOrders,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                ]);

                $generatedInvoices[] = $weeklyInvoice;
            }

            return response()->json([
                'message' => 'Weekly invoices generated successfully',
                'count' => count($generatedInvoices),
                'invoices' => $generatedInvoices
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate weekly invoices: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate weekly invoices'], 500);
        }
    }

    /**
     * Approve a weekly invoice
     */
    public function approve(Request $request, $id)
    {
        // Check if user has permission to approve seller invoices
        if (!Auth::user()->hasPermission('approve_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to approve seller invoices.'], 403);
        }

        $invoice = WeeklySellerInvoice::findOrFail($id);
        
        if ($invoice->status !== 'pending') {
            return response()->json(['error' => 'Invoice is not pending approval'], 400);
        }

        try {
            // Update invoice status first
            $invoice->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => $request->input('notes', ''),
            ]);

            // Generate PDF for approved invoice (after status update)
            $this->generatePdf($invoice);

            return response()->json([
                'message' => 'Weekly invoice approved successfully',
                'invoice' => $invoice->load('approver')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to approve weekly invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve weekly invoice'], 500);
        }
    }

    /**
     * Reject a weekly invoice
     */
    public function reject(Request $request, $id)
    {
        // Check if user has permission to reject seller invoices
        if (!Auth::user()->hasPermission('reject_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to reject seller invoices.'], 403);
        }

        $invoice = WeeklySellerInvoice::findOrFail($id);
        
        if ($invoice->status !== 'pending') {
            return response()->json(['error' => 'Invoice is not pending approval'], 400);
        }

        $invoice->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $request->input('notes', ''),
        ]);

        return response()->json([
            'message' => 'Weekly invoice rejected',
            'invoice' => $invoice->load('approver')
        ]);
    }

    /**
     * Download a weekly invoice PDF
     */
    public function download($id)
    {
        // Check if user has permission to download seller invoices
        if (!Auth::user()->hasPermission('download_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to download seller invoices.'], 403);
        }

        $invoice = WeeklySellerInvoice::findOrFail($id);

        // Allow download for approved invoices or for preview (pending invoices)
        if (!$invoice->isApproved() && !$invoice->isPending()) {
            return response()->json(['error' => 'Invoice must be approved or pending before download'], 403);
        }

        // For pending invoices, generate PDF on-the-fly for preview
        if ($invoice->isPending()) {
            try {
                $this->generatePdf($invoice);
            } catch (\Exception $e) {
                Log::error('Failed to generate weekly invoice PDF for preview: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Failed to generate invoice preview.',
                    'message' => 'Please try again or contact support.'
                ], 500);
            }
        } elseif (!Storage::exists($invoice->pdf_path)) {
            // For approved invoices, attempt to regenerate if missing
            try {
                $this->generatePdf($invoice);
            } catch (\Exception $e) {
                Log::error('Failed to regenerate weekly invoice PDF: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Invoice file is missing and could not be regenerated.',
                    'message' => 'Please contact support.'
                ], 404);
            }
        }

        return response()->download(Storage::path($invoice->pdf_path));
    }

    /**
     * Mark a weekly invoice as paid
     */
    public function markAsPaid($id)
    {
        // Check if user has permission to mark seller invoices as paid
        if (!Auth::user()->hasPermission('mark_seller_invoices_paid')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to mark seller invoices as paid.'], 403);
        }

        $invoice = WeeklySellerInvoice::findOrFail($id);

        if ($invoice->is_paid) {
            return response()->json(['error' => 'Invoice is already marked as paid'], 400);
        }

        if ($invoice->status !== 'approved') {
            return response()->json(['error' => 'Only approved invoices can be marked as paid'], 400);
        }

        try {
            $invoice->update([
                'is_paid' => true,
                'paid_at' => now(),
                'paid_by' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Invoice marked as paid successfully',
                'invoice' => $invoice->load(['approver', 'paidBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark weekly invoice as paid: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark invoice as paid'], 500);
        }
    }

    /**
     * Revoke payment status of a weekly invoice
     */
    public function revokePayment($id)
    {
        // Check if user has permission to mark seller invoices as paid (same permission for revoke)
        if (!Auth::user()->hasPermission('mark_seller_invoices_paid')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to revoke payment status.'], 403);
        }

        $invoice = WeeklySellerInvoice::findOrFail($id);

        if (!$invoice->is_paid) {
            return response()->json(['error' => 'Invoice is not marked as paid'], 400);
        }

        try {
            $invoice->update([
                'is_paid' => false,
                'paid_at' => null,
                'paid_by' => null,
            ]);

            return response()->json([
                'message' => 'Payment status revoked successfully',
                'invoice' => $invoice->load(['approver', 'paidBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to revoke payment for weekly invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to revoke payment status'], 500);
        }
    }

    /**
     * Delete a weekly invoice (superadmin only)
     */
    public function destroy($id)
    {
        // Check if user is superadmin
        if (!Auth::user()->isSuperadmin()) {
            return response()->json(['error' => 'Unauthorized. Only superadmin can delete weekly invoices.'], 403);
        }

        $invoice = WeeklySellerInvoice::findOrFail($id);

        try {
            // Delete the PDF file if it exists
            if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
                Storage::delete($invoice->pdf_path);
            }

            // Delete the invoice record
            $invoice->delete();

            return response()->json([
                'message' => 'Weekly invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete weekly invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete weekly invoice'], 500);
        }
    }

    /**
     * Generate PDF for weekly invoice
     */
    private function generatePdf(WeeklySellerInvoice $invoice)
    {
        // Get daily invoices for this seller in this week period
        $dailyInvoices = \App\Models\SellerInvoice::where('seller', $invoice->seller)
            ->whereBetween('invoice_date', [$invoice->week_start_date, $invoice->week_end_date])
            ->get();

        if ($dailyInvoices->isEmpty()) {
            throw new \Exception('No daily invoices found for this week period');
        }

        // Get all orders from these daily invoices
        $orderIds = [];
        foreach ($dailyInvoices as $dailyInvoice) {
            // Get orders for this specific invoice date
            $ordersForDate = Order::with(['product', 'orderStatus'])
                ->where('seller', $invoice->seller)
                ->whereHas('orderStatus', function ($q) {
                    $q->where('name', 'Delivered');
                })
                ->whereDate('updated_at', $dailyInvoice->invoice_date)
                ->get();
            
            $orderIds = array_merge($orderIds, $ordersForDate->pluck('id')->toArray());
        }

        // Get all orders for this seller in this week period
        $orders = Order::with(['product', 'orderStatus'])
            ->whereIn('id', $orderIds)
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            throw new \Exception('No delivered orders found for this week');
        }

        // Calculate totals
        $totalOrders = $orders->count();
        $sellerDeliveryPrice = \App\Models\Setting::getSellerDeliveryPrice();
        $sellerProductTotal = $orders->sum('price');
        $sellerDeliveryCostTotal = $totalOrders * $sellerDeliveryPrice;
        
        // Calculate purchase price total for company products only
        $sellerPurchasePriceTotal = 0;
        foreach ($orders as $order) {
            if ($order->product && $order->product->is_company_product) {
                $sellerPurchasePriceTotal += $order->product->purchase_price * $order->quantity;
            }
        }

        // Generate PDF path
        $filename = 'weekly-seller-invoice-' . Str::slug($invoice->seller) . '-' . $invoice->week_start_date . '.pdf';
        $pdfPath = 'invoices/sellers/weekly/' . $filename;

        // Ensure directory exists
        Storage::makeDirectory('invoices/sellers/weekly');

        $pdf = Pdf::loadView('pdf.weekly-seller-invoice', [
            'orders' => $orders,
            'invoice' => $invoice,
            'totalAmount' => $invoice->total_amount,
            'totalOrders' => $totalOrders,
            'productTotal' => $sellerProductTotal,
            'purchasePriceTotal' => $sellerPurchasePriceTotal,
            'deliveryCostTotal' => $sellerDeliveryCostTotal,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
            ]);

        Storage::put($pdfPath, $pdf->output());

        // Update invoice with PDF path
        $invoice->update(['pdf_path' => $pdfPath]);
    }
}