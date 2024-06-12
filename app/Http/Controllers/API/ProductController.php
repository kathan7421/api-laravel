<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Hash;
use Auth;
use Str;
use Storage;
use Validator;

class ProductController extends Controller
{
    //

    use ResponseTrait;

//     public function addItems(Request $request)
// {
//     try {
//         $inputs = $request->all();

//         // Validation rules
//         $rules = [
//             'name' => 'required|string|max:255',
//             // 'description' => 'required|nullable|string',
//             'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Correct validation rules
//         ];

//         // Validate request
//         $validator = Validator::make($inputs, $rules);
//         if ($validator->fails()) {
//             return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
//         }

//         // Create category
//         $cat = new Product();
//         $categoryData = $cat->prepareCreateData($inputs);

        
//         $category = Product::create($categoryData);

//         if ($category) {
//             return response()->json(['category' => $category, 'message' => 'Product added successfully'], 201); // 201 Created
//         }

//         return response()->json(['error' => 'Failed to add category'], 500); // 500 Internal Server Error
//     } catch (NotFoundHttpException $ex) {
//         return response()->json(['error' => $ex->getMessage()], 404); // 404 Not Found
//     } catch (Exception $ex) {
//         return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
//     }
// }
public function getItems($id)
	{
		
		try{
			$product = Product::where('id',$id)->first();
			if(!$product){
				return $this->notFoundRequest("Product Details not found");
			}
			return $this->successResponse($product->toArray(),"Product details");
		}catch(Exception $ex){
			return $this->sendErrorResponse($ex);
		}
	}
    public function addItems(Request $request)
    {
        try {
            $inputs = $request->all();
    
            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'qty' => 'required|integer',
                // 'category_id' => 'required|integer',
                'image' => 'nullable|string', // Accept base64 string
            ];
    
            // Validate request
            $validator = Validator::make($inputs, $rules);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
            }
    
            // Decode and save image if provided
            $imageName = null;
            if ($request->has('image') && $request->input('image')) {
                $imagePath = $this->base64ToImage($request->input('image'));
                $imageName = basename($imagePath);
            }
    
            $slug = Str::slug($inputs['name']);
            $sku = substr(str_shuffle('0123456789'), 0, 10); // Generates a 10-digit random string

            // Create product
            $productData = [
                'name' => $inputs['name'],
                'slug' => $slug,
                'description' => $inputs['description'],
                'price' => $inputs['price'],
                'qty' => $inputs['qty'],
                'category_id' => $inputs['category_id'],
                'image' => $imageName ? url('storage/' . $imagePath) : null, // Return the full URL
                'product_no' => $sku // Include the SKU
            ];
            
    
            $product = Product::create($productData);
    
            if ($product) {
                return response()->json(['product' => $product, 'message' => 'Product added successfully'], 201); // 201 Created
            }
    
            return response()->json(['error' => 'Failed to add product'], 500); // 500 Internal Server Error
        } catch (NotFoundHttpException $ex) {
            return response()->json(['error' => $ex->getMessage()], 404); // 404 Not Found
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
        }
    }
    
    /**
 * Decode base64 image and save it to storage.
 *
 * @param string $base64Image
 * @return string|null
 * @throws \Exception
 */
private function base64ToImage($base64Image)
{
    $image = explode('base64,', $base64Image);
    $image = end($image);
    $image = str_replace(' ', '+', $image);
    $file = "product/" . uniqid() . '.png';

    Storage::disk('public')->put($file, base64_decode($image));

    return $file;
}

public function updateItems(Request $request, $id)
{
    
    try {
        // Find the product by ID
        $product = Product::findOrFail($id);

        // Get all inputs
        $inputs = $request->all();

        // Validation rules
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'qty' => 'sometimes|required|integer',
            'category_id' => 'sometimes|required|integer',
            'image' => 'nullable|string', // Accept base64 string
        ];

        // Validate the request
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
        }

        // Decode and save image if provided
        if ($request->has('image') && $request->input('image')) {
            $imagePath = $this->base64ToImage($request->input('image'));
            $imageName = basename($imagePath);

            // Delete the old image if it exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Update the image path in the database
            $product->image = $imageName;
        }

        // Update other fields if provided
        $product->name = $inputs['name'] ?? $product->name;
        $product->description = $inputs['description'] ?? $product->description;
        $product->price = $inputs['price'] ?? $product->price;
        $product->qty = $inputs['qty'] ?? $product->qty;
        $product->category_id = $inputs['category_id'] ?? $product->category_id;

        // Save the changes
        $product->save();

        return response()->json(['product' => $product, 'message' => 'Product updated successfully'], 200); // 200 OK
    } catch (NotFoundHttpException $ex) {
        return response()->json(['error' => 'Product not found'], 404); // 404 Not Found
    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
    }
}

public function listItems(Request $request)
{
    try {
        $sortBy = $request->input('sortBy', 'id');
        $search = $request->input('search', null);
        $direction = strtoupper($request->input('sortDirection', 'ASC'));

        $query = Product::query(); // Start building the query
       
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $query->orderBy($sortBy, $direction);

        // Get all categories without pagination
        $categories = $query->get();

        return $this->successResponse($categories->toArray(), 'List of Categories');
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}
public function changeStatus(Request $request,$id){
    try
    {
        $product = Product::where('id',$id)->first();
        if($product){
        $product->status = ($product->status) == '1' ? '0' :'1';

        $product->save();
        return response()->json(['message'=>'Product Status updated',200]);
        }
        return response()->json(['message'=>'Product Status not updated',404]);


    }
    catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
}

public function deleteItems($id)
{
    try {
        $product = Product::find($id);

        if (!$product) {
            return $this->sendBadRequest('Product Not Found');
        }

        // Check if the image exists before attempting to delete it
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            // Delete the image file
            Storage::disk('public')->delete($product->image);
        }

        if ($product->delete()) {
            return $this->successResponse([], "Product Removed successfully");
        }

        return $this->sendBadRequest("Bad Request");
    } catch (NotFoundHttpException $ex) {
        return $this->notFoundRequest($ex);
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}
}

