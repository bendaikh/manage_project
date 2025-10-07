<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerInvoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;

class SellerInvoiceController extends Controller
{
    /**
     * List seller invoices (optionally filtered by seller role)
     */
    public function index(Request $request)
    {
        // Check if user has permission to view seller invoices
        if (!Auth::user()->hasPermission('view_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to view seller invoices.'], 403);
        }

        $perPage = $request->input('per_page', 10);
        $sellerFilter = $request->input('seller');

        $query = SellerInvoice::query()->orderByDesc('invoice_date');

        // If the authenticated user has role 'seller', limit to their invoices
        if (Auth::check() && Auth::user()->hasRole('seller')) {
            $query->where('seller', Auth::user()->name);
        }

        // Apply seller filter if provided
        if ($sellerFilter) {
            $query->where('seller', $sellerFilter);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Get list of unique sellers for filter dropdown
     */
    public function getSellers()
    {
        $sellers = SellerInvoice::distinct()
            ->pluck('seller')
            ->filter()
            ->sort()
            ->values();

        return response()->json($sellers);
    }

    /**
     * Download a seller invoice PDF, regenerating if missing
     */
    public function download($id)
    {
        // Check if user has permission to download seller invoices
        if (!Auth::user()->hasPermission('download_seller_invoices')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to download seller invoices.'], 403);
        }

        $invoice = SellerInvoice::findOrFail($id);

        if (!Storage::exists($invoice->pdf_path)) {
            // Attempt to regenerate
            try {
                $this->regeneratePdf($invoice);
            } catch (\Exception $e) {
                Log::error('Failed to regenerate seller invoice PDF: ' . $e->getMessage());
                return response()->json([
                    'error'   => 'Invoice file is missing and could not be regenerated.',
                    'message' => 'Please contact support.'
                ], 404);
            }
        }

        return response()->download(Storage::path($invoice->pdf_path));
    }

    /**
     * Regenerate a missing PDF and update invoice record
     */
    private function regeneratePdf(SellerInvoice $invoice)
    {
        // Fetch delivered orders for this seller on that date
        $orders = Order::with(['product', 'orderStatus'])
            ->where('seller', $invoice->seller)
            ->whereHas('orderStatus', function ($q) {
                $q->where('name', 'Delivered');
            })
            ->whereDate('updated_at', $invoice->invoice_date)
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            throw new \Exception('No delivered orders found');
        }

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
        
        $totalAmount = $sellerProductTotal - $sellerPurchasePriceTotal - $sellerDeliveryCostTotal;
        if ($totalAmount < 0) { $totalAmount = 0; }
        $today       = $invoice->invoice_date;

        // Ensure directory exists
        Storage::makeDirectory('invoices/sellers');

        $pdf = Pdf::loadView('pdf.seller-invoice', [
            'orders' => $orders,
            'totalAmount' => $totalAmount,
            'totalOrders' => $totalOrders,
            'productTotal' => $sellerProductTotal,
            'purchasePriceTotal' => $sellerPurchasePriceTotal,
            'deliveryCostTotal' => $sellerDeliveryCostTotal,
            'today' => $today,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'     => true,
                'defaultFont'         => 'DejaVu Sans',
                'dpi'                 => 150,
            ]);

        Storage::put($invoice->pdf_path, $pdf->output());

        // Update totals to keep in sync
        $invoice->update([
            'order_count' => $totalOrders,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Mark a seller invoice as paid
     */
    public function markAsPaid($id)
    {
        // Check if user has permission to mark seller invoices as paid
        if (!Auth::user()->hasPermission('mark_seller_invoices_paid')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to mark seller invoices as paid.'], 403);
        }

        $invoice = SellerInvoice::findOrFail($id);

        if ($invoice->is_paid) {
            return response()->json(['error' => 'Invoice is already marked as paid'], 400);
        }

        try {
            $invoice->update([
                'is_paid' => true,
                'paid_at' => now(),
                'paid_by' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Invoice marked as paid successfully',
                'invoice' => $invoice->load('paidBy')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark seller invoice as paid: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark invoice as paid'], 500);
        }
    }

    /**
     * Revoke payment status of a seller invoice
     */
    public function revokePayment($id)
    {
        // Check if user has permission to mark seller invoices as paid (same permission for revoke)
        if (!Auth::user()->hasPermission('mark_seller_invoices_paid')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to revoke payment status.'], 403);
        }

        $invoice = SellerInvoice::findOrFail($id);

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
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to revoke payment for seller invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to revoke payment status'], 500);
        }
    }

    /**
     * Delete a seller invoice (superadmin only)
     */
    public function destroy($id)
    {
        // Check if user is superadmin
        if (!Auth::user()->isSuperadmin()) {
            return response()->json(['error' => 'Unauthorized. Only superadmin can delete seller invoices.'], 403);
        }

        $invoice = SellerInvoice::findOrFail($id);

        try {
            // Delete the PDF file if it exists
            if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
                Storage::delete($invoice->pdf_path);
            }

            // Delete the invoice record
            $invoice->delete();

            return response()->json([
                'message' => 'Seller invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete seller invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete seller invoice'], 500);
        }
    }
} 