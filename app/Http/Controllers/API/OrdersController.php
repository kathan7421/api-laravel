<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Traits\ResponseTrait;


class OrdersController extends Controller
{
    use ResponseTrait;

	
public function listItems(Request $request)
{
    try {
        $sortBy = $request->input('sortBy', 'id');
        $search = $request->input('search', null);
        $direction = strtoupper($request->input('sortDirection', 'ASC'));

        $query = Orders::query(); // Start building the query
        
        if ($search) {
            $query->where('order_number', 'like', '%' . $search . '%');
        }

        $query->orderBy($sortBy, $direction);

        // Get all categories without pagination
        $orders = $query->get();

        return $this->successResponse($orders->toArray(), 'List of Orders');
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}
public function getOrderStatusCounts()
    {
        try {
            $counts = Orders::selectRaw("status, COUNT(*) as count")
                ->groupBy('status')
                ->pluck('count', 'status');
                
            return response()->json([
                'success' => true,
                'data' => $counts
            ], 200);
        } catch (Exception $ex) {
            return $this->sendErrorResponse($ex);
        }
    }
}
