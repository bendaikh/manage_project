<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryInvoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class DeliveryInvoiceController extends Controller
{
    public function index()
    {
        // Paginate invoices, newest first
        $perPage = request()->input('per_page', 10);
        $invoices = DeliveryInvoice::orderByDesc('invoice_date')->paginate($perPage);
        return response()->json($invoices);
    }

    public function download($id)
    {
        try {
            $invoice = DeliveryInvoice::findOrFail($id);
            
            // Check if PDF file exists
            if (!Storage::exists($invoice->pdf_path)) {
                // Try to regenerate the PDF
                try {
                    $this->regeneratePdf($invoice);
                    
                    // Check again if the file exists after regeneration
                    if (!Storage::exists($invoice->pdf_path)) {
                        return response()->json([
                            'error' => 'Invoice file could not be generated. Please contact support.',
                            'message' => 'The PDF file for this invoice is missing and could not be regenerated.'
                        ], 404);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to regenerate PDF for invoice ' . $id . ': ' . $e->getMessage());
                    return response()->json([
                        'error' => 'Invoice file is missing and could not be regenerated.',
                        'message' => 'Please contact support to resolve this issue.'
                    ], 404);
                }
            }
            
            // Get the full path to the file
            $fullPath = Storage::path($invoice->pdf_path);
            
            // Double-check that the file actually exists on disk
            if (!file_exists($fullPath)) {
                return response()->json([
                    'error' => 'Invoice file not found on disk.',
                    'message' => 'The PDF file exists in the database but not on the server. Please contact support.'
                ], 404);
            }
            
            return Response::download($fullPath, basename($invoice->pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . basename($invoice->pdf_path) . '"',
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Invoice not found.',
                'message' => 'The requested invoice could not be found in the database.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error downloading invoice ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while downloading the invoice.',
                'message' => 'Please try again or contact support if the problem persists.'
            ], 500);
        }
    }
    
    private function regeneratePdf(DeliveryInvoice $invoice)
    {
        try {
            // Get orders for that specific date
            $orders = \App\Models\Order::with(['product', 'orderStatus'])
                ->whereHas('orderStatus', function ($query) {
                    $query->where('name', 'Delivered');
                })
                ->whereDate('updated_at', $invoice->invoice_date)
                ->orderBy('updated_at', 'desc')
                ->get();
                
            if ($orders->isEmpty()) {
                throw new \Exception('No delivered orders found for this date');
            }
            
            $deliveryPrice = \App\Models\Setting::getDeliveryPrice();
            $totalOrders = $orders->count();
            $productTotal = $orders->sum('price');
            $deliveryCostTotal = $totalOrders * $deliveryPrice;
            $totalAmount = $productTotal - $deliveryCostTotal;
            if ($totalAmount < 0) { $totalAmount = 0; }
            $today = $invoice->invoice_date;
            
            // Ensure the invoices directory exists
            Storage::makeDirectory('invoices');
            
            // Generate PDF
            $pdf = Pdf::loadView('pdf.delivery-invoice', compact('orders', 'totalAmount', 'totalOrders', 'today', 'productTotal', 'deliveryCostTotal'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'dpi' => 150,
                ]);
            
            // Save PDF to storage
            $saved = Storage::put($invoice->pdf_path, $pdf->output());
            
            if (!$saved) {
                throw new \Exception('Failed to save PDF file to storage');
            }
            
            // Verify the file was actually saved
            if (!Storage::exists($invoice->pdf_path)) {
                throw new \Exception('PDF file was not found after saving');
            }
            
            // Update the invoice record with current data
            $invoice->update([
                'order_count' => $totalOrders,
                'total_amount' => $totalAmount,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to regenerate PDF for invoice ' . $invoice->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
