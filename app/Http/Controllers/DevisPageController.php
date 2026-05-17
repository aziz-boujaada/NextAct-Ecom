<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDevisRequest;
use App\Http\Requests\UpdateDevisRequest;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Product;
use App\Models\User;
use App\Services\DevisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DevisPageController extends Controller
{
    public function __construct(private readonly DevisService $devisService) {}

    public function index(Request $request): View
    {
        $query = Devis::with(['client', 'items.product', 'createdBy']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $devises = $query->latest()->paginate(10)->withQueryString();

        return view('devis.index', compact('devises'));
    }

    public function create(): View
    {
        return view('devis.create', [
            'devis' => new Devis(['status' => 'draft']),
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'items' => [[]],
        ]);
    }

    public function store(StoreDevisRequest $request): RedirectResponse
    {
        $devis = $this->devisService->create([
            ...$request->validated(),
            'created_by' => $request->user()?->id ?? User::query()->value('id') ?? 1,
        ]);

        return redirect()->route('devis.show.page', $devis)->with('success', 'Devis created successfully.');
    }

    public function show(Devis $devis): View
    {
        $devis->load(['client', 'createdBy', 'items.product']);

        return view('devis.show', [
            'devis' => $devis,
            'timeline' => $this->timeline($devis),
        ]);
    }

    public function edit(Devis $devis): View
    {
        $devis->load(['client', 'items.product']);

        return view('devis.edit', [
            'devis' => $devis,
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'items' => $devis->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ])->values()->all() ?: [[]],
        ]);
    }

    public function update(UpdateDevisRequest $request, Devis $devis): RedirectResponse
    {
        $this->devisService->update($devis, $request->validated());

        return redirect()->route('devis.show.page', $devis)->with('success', 'Devis updated successfully.');
    }

    public function destroy(Devis $devis): RedirectResponse
    {
        $this->devisService->delete($devis);

        return redirect()->route('devis.index.page')->with('success', 'Devis deleted successfully.');
    }

    public function send(Devis $devis): RedirectResponse
    {
        $this->devisService->send($devis);

        return back()->with('success', 'Devis sent successfully.');
    }

    public function accept(Devis $devis): RedirectResponse
    {
        $this->devisService->acceptAndConvert($devis);

        return back()->with('success', 'Devis accepted and converted to sale successfully.');
    }

    public function reject(Devis $devis): RedirectResponse
    {
        $this->devisService->reject($devis);

        return back()->with('success', 'Devis rejected successfully.');
    }

    public function convertToSale(Devis $devis): RedirectResponse
    {
        $this->devisService->acceptAndConvert($devis);

        return back()->with('success', 'Devis converted to sale successfully.');
    }

    public function pdf(Devis $devis)
    {
        $devis->load(['client', 'items.product']);

        $pdf = Pdf::loadView('devis.pdf', compact('devis'))->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $devis->reference . '.pdf"',
        ]);
    }

    private function timeline(Devis $devis): array
    {
        return collect(['draft', 'sent', 'accepted', 'rejected', 'expired'])->map(fn ($status) => [
            'status' => $status,
            'label' => ucfirst($status),
            'active' => $devis->status === $status,
        ])->all();
    }
}
