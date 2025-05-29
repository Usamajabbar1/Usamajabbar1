<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyProfile;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\InvoiceResource;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['items', 'customer', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return InvoiceResource::collection($invoices);
    }

    public function download($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $company = CompanyProfile::first();

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'company'));

        return $pdf->download("invoice_{$invoice->invoice_number}.pdf");
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    public function show($id)
    {
        $invoice = Invoice::with('items', 'customer')->findOrFail($id);
        $company = CompanyProfile::first();

        return response()->json([
            'invoice' => $invoice,
            'company' => $company,
        ]);
    }

    public function sendEmail($id)
    {
        $invoice = Invoice::with(['items', 'customer'])->findOrFail($id);

        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));

        return response()->json(['message' => 'Invoice sent successfully.']);
    }

    // Store: Auto-generate unique invoice number on creation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Generate unique invoice number with prefix INV- and zero-padded 4 digits
        do {
            $latestInvoice = Invoice::latest('id')->first();
            $nextNumber = $latestInvoice ? ((int) str_replace('INV-', '', $latestInvoice->invoice_number) + 1) : 1;
            $invoiceNumber = 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        $validated['invoice_number'] = $invoiceNumber;

        $invoice = Invoice::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        foreach ($validated['items'] as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => $quantity * $unitPrice,
            ]);
        }

        return new InvoiceResource($invoice->load(['items', 'customer']));
    }

    // Update: invoice_number is NOT editable here
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            // Remove invoice_number validation, because it should be immutable
            'invoice_date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            // invoice_number not updated here
            'invoice_date' => $validated['invoice_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'customer_id' => $validated['customer_id'],
        ]);

        // Delete old items and recreate
        $invoice->items()->delete();

        foreach ($validated['items'] as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => $quantity * $unitPrice,
            ]);
        }

        return new InvoiceResource($invoice->load(['items', 'customer']));
    }

    // Optional: Download PDF method (same as download)
    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $company = CompanyProfile::first();

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'company'));

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }
}
