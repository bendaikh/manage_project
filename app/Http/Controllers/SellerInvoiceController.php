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
        $perPage = $request->input('per_page', 10);

        $query = SellerInvoice::query()->orderByDesc('invoice_date');

        // If the authenticated user has role 'seller', limit to their invoices
        if (Auth::check() && Auth::user()->hasRole('seller')) {
            $query->where('seller', Auth::user()->name);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Download a seller invoice PDF, regenerating if missing
     */
    public function download($id)
    {
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
        $totalAmount = $sellerProductTotal - $sellerDeliveryCostTotal;
        if ($totalAmount < 0) { $totalAmount = 0; }
        $today       = $invoice->invoice_date;

        // Ensure directory exists
        Storage::makeDirectory('invoices/sellers');

        $pdf = Pdf::loadView('pdf.seller-invoice', [
            'orders' => $orders,
            'totalAmount' => $totalAmount,
            'totalOrders' => $totalOrders,
            'productTotal' => $sellerProductTotal,
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
} 