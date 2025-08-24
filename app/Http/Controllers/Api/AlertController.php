<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int)$request->query('per_page', 15), 100));
        $q = Alert::where('user_id', $request->user()->id)
            ->orderByDesc('created_at');
        if ($request->filled('only_unread')) {
            $q->whereNull('read_at');
        }
        return response()->json($q->paginate($perPage));
    }

    public function unreadCount(Request $request)
    {
        $count = Alert::where('user_id', $request->user()->id)
            ->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, Alert $alert)
    {
        if ($alert->user_id !== $request->user()->id) {
            return response()->json(['message'=>'Not found'], 404);
        }
        if (!$alert->read_at) $alert->update(['read_at' => now()]);
        return response()->json(['data'=>$alert]);
    }
}
