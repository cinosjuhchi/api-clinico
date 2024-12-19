<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $clinic = match ($user->role) {
            'clinic' => $user->clinic,
            'doctor' => $user->doctor->clinic,
            'staff' => $user->staff->clinic,
            default => abort(401, 'Unauthorized access. Invalid role.'),
        };

        if (!$clinic) {
            $clinic = $user->doctor->clinic;
            if (!$clinic) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'user not found',
                ]);
            }
        }

        // GET: Medicines & Injections
        $medicines = $clinic->medications()->get()->map(function($medicine) {
            if ($medicine->total_amount <= 0) {
                $medicine->status = 'OUT OF STOCK';
            } else if ($medicine->expired_date < Carbon::now()->toDateString()) {
                $medicine->status = 'EXPIRED';
            } else if ($medicine->total_amount > 0) {
                $medicine->status = 'AVAILABLE';
            }
            return $medicine;
        });

        $injections = $clinic->injections()->get()->map(function($injection) {
            if ($injection->total_amount <= 0) {
                $injection->status = 'OUT OF STOCK';
            } else if ($injection->expired_date < Carbon::now()->toDateString()) {
                $injection->status = 'EXPIRED';
            } else if ($injection->total_amount > 0) {
                $injection->status = 'AVAILABLE';
            }
            return $injection;
        });

        $data = $medicines
                ->sortBy('name')
                ->merge($injections)
                ->values();

        // PAGINATE
        $page = request()->get('page', 1);
        $perPage = 20;
        $total = $data->count();
        $pagedData = $data->slice(($page - 1) * $perPage, $perPage)->values();

        $paginatedData = new LengthAwarePaginator(
            $pagedData,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Success to get inventory data.',
            'total_stock' => $data->sum('total_amount'),
            'data' => $paginatedData
        ]);
    }
}
