<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\PurchaseInvoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{

  public function __construct()
  {
    $this->middleware('permissions:generate_invoices')->only(['generateInvoice', 'generatePurchaseInvoice']);
  }
  public function generateInvoice($id)
  {
    $sale = Sale::with(['client', 'items.product', 'invoice'])->findOrFail($id);
    $companyInfo = CompanySetting::first();

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
      'companyInfo' => $companyInfo,
    ])->setPaper('a4');

    return response($pdf->output(), 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.pdf"',
    ]);
  }

  public function generatePurchaseInvoice($id)
  {
    $purchase = Purchase::with(['supplier', 'items.product', 'invoice'])->findOrFail($id);

    $invoice = PurchaseInvoice::updateOrCreate(
      ['purchase_id' => $purchase->id],
      [
        'invoice_number' => 'PINV-' . str_pad($purchase->id, 6, '0', STR_PAD_LEFT),
        'total' => $purchase->total,
      ]
    );

    $purchase->setRelation('invoice', $invoice);

    $pdf = Pdf::loadView('invoices.purchase', [
      'purchase' => $purchase,
      'invoice' => $invoice,
    ])->setPaper('a4');

    return response($pdf->output(), 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.pdf"',
    ]);
  }
}
