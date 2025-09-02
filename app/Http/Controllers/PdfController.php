<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\DeliveryInvoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function deliveryNote(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $orders = Order::with(['product', 'orderStatus'])->whereIn('id', $ids)->get();
        if ($orders->isEmpty()) {
            abort(404, 'Orders not found');
        }
        $totalPrice = $orders->sum('price');
        $shippingTotal = count($orders) * 1500;
        $grandTotal = $totalPrice - $shippingTotal;
        
        $pdf = Pdf::loadView('pdf.delivery-note', compact('orders', 'totalPrice', 'shippingTotal', 'grandTotal'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
            ]);
        return $pdf->download('delivery-note.pdf');
    }

    public function invoices(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $orders = Order::with(['product', 'orderStatus'])->whereIn('id', $ids)->get();
        if ($orders->isEmpty()) {
            abort(404, 'Orders not found');
        }
        
        $pdf = Pdf::loadView('pdf.invoices', compact('orders'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
            ]);
        return $pdf->download('invoices.pdf');
    }

    public function deliveryInvoice(Request $request)
    {
        // Allow lengthy PDF generation
        @set_time_limit(300); // 5 minutes
        @ini_set('memory_limit', '512M');

        // Use explicit timezone handling to ensure correct date
        $today = now()->setTimezone('UTC')->toDateString();
        $ids = array_filter(explode(',', $request->query('ids', '')));

        if ($ids) {
            // If specific IDs are provided, use them and include multiple statuses
            $ordersQuery = Order::with(['product', 'orderStatus'])
                ->whereHas('orderStatus', function ($query) {
                    $query->whereIn('name', ['Confirmed', 'Delivered', 'Shipped', 'Processing']);
                })
                ->whereIn('id', $ids);
        } else {
            // If no IDs provided, automatically get all delivered orders from today
            $ordersQuery = Order::with(['product', 'orderStatus'])
                ->whereHas('orderStatus', function ($query) {
                    $query->where('name', 'Delivered');
                })
                ->whereDate('created_at', $today);
        }

        $orders = $ordersQuery->orderBy('updated_at', 'desc')->get();
        if ($orders->isEmpty()) {
            if ($ids) {
                abort(404, 'No orders found with the specified IDs');
            } else {
                abort(404, 'No delivered orders found for today');
            }
        }

        // Update order status to "Shipped"
        /* $shippedStatus = \App\Models\OrderStatus::where('name', 'Shipped')->first();
        if ($shippedStatus) {
            foreach ($orders as $order) {
                $order->order_status_id = $shippedStatus->id;
                $order->save();
            }
        } */

        $deliveryPrice = \App\Models\Setting::getDeliveryPrice();
        $totalOrders = $orders->count();
        $productTotal = $orders->sum('price');
        $deliveryCostTotal = $totalOrders * $deliveryPrice;
        $totalAmount = $productTotal - $deliveryCostTotal;
        if ($totalAmount < 0) { $totalAmount = 0; }
        $filename = 'delivery-invoice-' . $today . '.pdf';
        $pdfPath = 'invoices/' . $filename;
        
        // Configure PDF options for better encoding support
        $pdf = Pdf::loadView('pdf.delivery-invoice', compact('orders', 'totalAmount', 'totalOrders', 'today', 'productTotal', 'deliveryCostTotal'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'enable_php' => true,
                'chroot' => public_path(),
                'dpi' => 150,
                'defaultPaperSize' => 'a4',
                'defaultPaperOrientation' => 'portrait',
            ]);
        
        try {
            // Ensure the invoices directory exists
            Storage::makeDirectory('invoices');
            
            // Save PDF to storage/app/invoices
            $saved = Storage::put($pdfPath, $pdf->output());
            
            if (!$saved) {
                throw new \Exception('Failed to save PDF file to storage');
            }
            
            // Verify the file was actually saved
            if (!Storage::exists($pdfPath)) {
                throw new \Exception('PDF file was not found after saving');
            }
            
            // Save or update invoice record
            DeliveryInvoice::updateOrCreate(
                ['invoice_date' => $today],
                [
                    'order_count' => $totalOrders,
                    'total_amount' => $totalAmount,
                    'pdf_path' => $pdfPath,
                ]
            );

            /* Generate seller-level invoices */
            $ordersBySeller = $orders->groupBy('seller');
            $sellerDeliveryPrice = \App\Models\Setting::getSellerDeliveryPrice();
            foreach ($ordersBySeller as $sellerName => $sellerOrders) {
                $sellerOrderCount   = $sellerOrders->count();
                $sellerProductTotal = $sellerOrders->sum('price');
                $sellerDeliveryCostTotal = $sellerOrderCount * $sellerDeliveryPrice;
                $sellerTotalAmount  = $sellerProductTotal - $sellerDeliveryCostTotal;
                if ($sellerTotalAmount < 0) { $sellerTotalAmount = 0; }
                $sellerFileName     = 'seller-invoice-' . Str::slug($sellerName) . '-' . $today . '.pdf';
                $sellerPdfPath      = 'invoices/sellers/' . $sellerFileName;

                // Ensure directory exists
                Storage::makeDirectory('invoices/sellers');

                $sellerPdf = Pdf::loadView('pdf.seller-invoice', [
                    'orders'               => $sellerOrders,
                    'totalAmount'          => $sellerTotalAmount,
                    'totalOrders'          => $sellerOrderCount,
                    'productTotal'         => $sellerProductTotal,
                    'deliveryCostTotal'    => $sellerDeliveryCostTotal,
                    'today'                => $today,
                ])->setPaper('a4', 'portrait')->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'     => true,
                    'defaultFont'         => 'DejaVu Sans',
                    'dpi'                 => 150,
                ]);

                // Save seller PDF
                Storage::put($sellerPdfPath, $sellerPdf->output());

                // Save/update record in DB
                \App\Models\SellerInvoice::updateOrCreate(
                    [
                        'seller'       => $sellerName,
                        'invoice_date' => $today,
                    ],
                    [
                        'order_count'  => $sellerOrderCount,
                        'total_amount' => $sellerTotalAmount,
                        'pdf_path'     => $sellerPdfPath,
                    ]
                );
            }
            // Return global invoice download response
            return response()->make($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate delivery invoice: ' . $e->getMessage());
            abort(500, 'Failed to generate delivery invoice. Please try again.');
        }
    }
} 