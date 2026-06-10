<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Resources\Api\WalletTransactionResource;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $query = $request->user()->walletTransactions()->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return WalletTransactionResource::collection($query->paginate($perPage));
    }
}
