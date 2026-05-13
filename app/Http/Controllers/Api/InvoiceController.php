<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{

  public function __construct()
  {
    $this->middleware('permissions:generate_invoices')->only(['generateInvoice']);
  }
  public function generateInvoice($id)
  {
    $sale = Sale::with(['client', 'items.product', 'invoice'])->findOrFail($id);

    $invoice = Invoice::updateOrCreate(
      ['sale_id' => $sale->id],
      [
        'invoice_number' => 'INV-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
        'total' => $sale->total,
      ]
    );

    $sale->setRelation('invoice', $invoice);

    $pdf = Pdf::loadView('invoices.invoice', [
      'sale' => $sale,
      'invoice' => $invoice,
    ])->setPaper('a4');

    return response($pdf->output(), 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.pdf"',
    ]);
  }
}
