<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'clients' => Client::latest()->get(),
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        $client = Client::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully',
            'client' => $client,
        ], 201);
    }

    public function show(Client $client)
    {
        return response()->json([
            'status' => 'success',
            'client' => $client,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully',
            'client' => $client->fresh(),
        ]);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully',
        ]);
    }
}
