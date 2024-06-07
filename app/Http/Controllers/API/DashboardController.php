<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Orders;
use App\Models\Category;
use App\Models\Product;
use App\Traits\ResponseTrait;

class DashboardController extends Controller
{
    use ResponseTrait;
    public function getCounts(){
        try{
            $userCount = User::where('user_type','1')->count();
            $orderCount = Orders::count();
            $categoryCount = Category::count();
            $productCount = Product::count();

            $data = [
                'users'=>  $userCount,
                'orders'=> $orderCount,
                'categories'=> $categoryCount,
                'products' =>  $productCount,
            ];
            return response()->json(['success'=>true,'data' => $data,],200); 
        }catch (NotFoundHttpException $ex) {
            return response()->json(['error' => 'Product not found'], 404); // 404 Not Found
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
        }
    }
}
