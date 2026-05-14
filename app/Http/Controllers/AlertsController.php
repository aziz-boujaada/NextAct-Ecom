<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Alerts;
use App\Models\Product;
use App\Services\AlertsService;
use Illuminate\Http\Request;

class AlertsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAlerts(Request $request)
    {
        $user = $request->user();

        $alerts = Alert::with(['product'])
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->with(['users' => function ($q) use ($user) {
                $q->where('users.id', $user->id);
            }])
            ->latest()
            ->paginate(20);

        $data = $alerts->map(function ($alert) {
            return [
                'id' => $alert->id,
                'type' => $alert->type,
                'stock' => $alert->stock,
                'alert_stock' => $alert->alert_stock,
                'product' => $alert->product->name,
                'is_read' => $alert->users->first()?->pivot->is_read,
                'created_at' => $alert->created_at,
            ];
        });


        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
    public function markAsRead(Request $request, Alert $alert)
    {
        $userId = $request->user()->id;

        $alert->users()->updateExistingPivot($userId, [
            'is_read' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alert marked as read'
        ]);
    }
}
