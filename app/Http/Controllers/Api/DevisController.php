<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDevisRequest;
use App\Http\Requests\UpdateDevisRequest;
use App\Models\Devis;
use App\Services\DevisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DevisController extends Controller
{
    public function __construct(private readonly DevisService $devisService)
    {
        $this->middleware('permissions:view_devis')->only(['index', 'show', 'pdf']);
        $this->middleware('permissions:create_devis')->only(['store']);
        $this->middleware('permissions:edit_devis')->only(['update']);
        $this->middleware('permissions:delete_devis')->only(['destroy']);
        $this->middleware('permissions:send_devis')->only(['send']);
        $this->middleware('permissions:accept_devis')->only(['accept']);
        $this->middleware('permissions:reject_devis')->only(['reject']);
        $this->middleware('permissions:convert_devis')->only(['convertToSale']);
    }

    public function index(Request $request)
    {
        $query = Devis::with(['client', 'createdBy', 'items.product']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items.product', fn ($product) => $product->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->integer('client_id')) {
            $query->where('client_id', $clientId);
        }

        $perPage = max(1, min(100, (int) $request->integer('per_page', 15)));
        $devises = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => 'DEVIS SEND WITH DATA ',
            'devises' => $devises,
        ]);
    }

    public function store(StoreDevisRequest $request)
    {
        $devis = $this->devisService->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Devis created successfully',
            'devis' => $devis,
        ], 201);
    }

    public function show(Devis $devis)
    {
        $devis = $devis->load(['client', 'createdBy', 'items.product']);

        return response()->json([
            'status' => 'success',
            'devis' => $devis,
            'timeline' => $this->timeline($devis),
        ]);
    }

    public function update(UpdateDevisRequest $request, Devis $devis)
    {
        $devis = $this->devisService->update($devis, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Devis updated successfully',
            'devis' => $devis,
        ]);
    }

    public function destroy(Devis $devis)
    {
        $this->devisService->delete($devis);

        return response()->json([
            'status' => 'success',
            'message' => 'Devis deleted successfully',
        ]);
    }

    public function send(Devis $devis)
    {
        $devis = $this->devisService->send($devis);

        return response()->json([
            'status' => 'success',
            'message' => 'Devis sent successfully',
            'devis' => $devis,
        ]);
    }

    public function accept(Devis $devis)
    {
        $result = $this->devisService->acceptAndConvert($devis);

        return response()->json([
            'status' => 'success',
            'message' => 'Devis accepted and converted to sale successfully',
            'devis' => $result['devis'],
            'sale' => $result['sale'],
        ]);
    }

    public function reject(Devis $devis)
    {
        $devis = $this->devisService->reject($devis);

        return response()->json([
            'status' => 'success',
            'message' => 'Devis rejected successfully',
            'devis' => $devis,
        ]);
    }

    public function convertToSale(Devis $devis)
    {
        $result = $this->devisService->acceptAndConvert($devis);

        return response()->json([
            'status' => 'success',
            'message' => 'Devis converted to sale successfully',
            'devis' => $result['devis'],
            'sale' => $result['sale'],
        ]);
    }

    public function pdf(Devis $devis)
    {
        $devis = $devis->load(['client', 'createdBy', 'items.product']);

        $pdf = Pdf::loadView('devis.pdf', [
            'devis' => $devis,
        ])->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $devis->reference . '.pdf"',
        ]);
    }

    private function timeline(Devis $devis): array
    {
        $steps = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

        return collect($steps)->map(fn ($status) => [
            'status' => $status,
            'label' => ucfirst($status),
            'active' => $devis->status === $status,
        ])->all();
    }
}
