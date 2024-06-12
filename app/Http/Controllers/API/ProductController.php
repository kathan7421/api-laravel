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

public function getItems($id)
{
    try {
        // Find the product by ID
        $product = Product::find($id);

        // Check if product exists
        if (!$product) {
            return $this->notFoundRequest("Product Details not found");
        }

        // Use the product model's toArray method to include accessor-modified attributes
        // Pass false as the second argument to get only the image name
        $data = $product->toArray();
        // $data['image'] = $product->getImageAttribute($product->image, false);

        return $this->successResponse($data, "Product details");
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}
private function generateUniqueSku()
{
    $sku = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

    if (Product::where('sku', $sku)->exists()) {
        return $this->generateUniqueSku(); // Recursively call until a unique SKU is found
    }

    return $sku;
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
            'sku' => 'nullable|string|max:10',
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
        $sku = $inputs['sku'] ?? $this->generateUniqueSku();
       
        // Create product
        $productData = [
            'name' => $inputs['name'],
            'slug' => $slug,
            'description' => $inputs['description'],
            'price' => $inputs['price'],
            'qty' => $inputs['qty'],
            'category_id' => $inputs['category_id'],
            'sku' => $sku,
            'image' => $imageName , // Return the full URL
          
        ];
        

        $product = Product::create($productData);

        if ($product) {
            return response()->json(['product' => $product, 'message' => 'Product added successfully'], 200); // 201 Created
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
    // Extract the base64 content
    $image = explode('base64,', $base64Image);
    $image = end($image);
    $image = str_replace(' ', '+', $image);

    // Generate a unique file name
    $fileName = uniqid() . '.png';
    $filePath = "product/" . $fileName;

    // Save the image to the specified disk (public)
    Storage::disk('public')->put($filePath, base64_decode($image));

    // Return the file name
    return $fileName;
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
            $imageName = $this->base64ToImage($request->input('image'));

            // Delete the old image if it exists
            if ($product->image) {
                Storage::disk('public')->delete('product/' . $product->image);
            }

            // Update the image name in the database
            $product->image = $imageName;
        }
        else {
            $request->image = null;
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
        $products = $query->get();

        return $this->successResponse($products->toArray(), 'List of Products');
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

