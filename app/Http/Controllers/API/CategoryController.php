<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Hash;
use Auth;
use Str;
use Storage;
use Validator;



class CategoryController extends Controller
{

	use ResponseTrait;

	
public function listItems(Request $request)
{
    try {
        $sortBy = $request->input('sortBy', 'id');
        $search = $request->input('search', null);
        $direction = strtoupper($request->input('sortDirection', 'ASC'));

        $query = Category::query(); // Start building the query
        
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

public function getItems($id)
	{
		
		try{
			$cat = Category::where('id',$id)->first();
			if(!$cat){
				return $this->notFoundRequest("Category Details not found");
			}
			return $this->successResponse($cat->toArray(),"Category details");
		}catch(Exception $ex){
			return $this->sendErrorResponse($ex);
		}

	}
public function changeStatus(Request $request,$id){
        try
        {
            $categories = Category::where('id',$id)->first();
            if($categories){
            $categories->status = ($categories->status) == '1' ? '0' :'1';

            $categories->save();
            return response()->json(['message'=>'Cate Status updated',200]);
            }
            return response()->json(['message'=>'Cate Status not updated',404]);


        }
        catch(Exception $ex){
			return $this->sendErrorResponse($ex);
		}
    }
// public function updateData(Request $request,$id)
// 	{
// 		try{

// 			$users = User::where('id',$id)->first();
// 			if(!$users)
// 			{
// 				return $this->sendBadRequest('Request Not Found');
// 			}
// 			$inputs = $request->all();
// 			$rules = [
// 				'name'=>'required',
// 				'email'=>'required',
// 				'password'=>'required',
// 			];
// 			$message =  [

// 				'name.required'=>'Name Field  Is Required',
// 				'email.required'=>'Email Field Is Required',
// 				'password.required'=>'Password Field Is Required',

// 			];

// 			$validator = Validator::make($inputs,$rules,$message);
// 			if($validator->fails())
// 			{
// 				return $this->sendBadRequest(implode(',',$validator->errors()->all()));
// 			}
// 					// print_r($user);die;
// 			$preparedata = $users->prepareUpdateData($inputs,$users);
// 			foreach($preparedata as $key=>$value)
// 			{
// 				$users->$key = $value;
// 			}
// 				// print_r($users->toArray());die;	
// 			if($users->save())
// 			{
// 				$msg = "Request Updated successfully";
// 					// print_r($users->toArray());die;
// 				return $this->successResponse([],$msg);

// 			}
// 		}
// 		catch(NotFoundHttpException $ex) 
// 		{
// 			return $this->notFoundRequest($ex);
// 		}catch(Execption $ex){
// 			return $this->sendErrorResponse($ex);
// 		}
// 	}
public function deleteItems($id)
{
    try {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendBadRequest('Category Not Found');
        }

        // Check if the image exists before attempting to delete it
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            // Delete the image file
            Storage::disk('public')->delete($category->image);
        }

        if ($category->delete()) {
            return $this->successResponse([], "Category Removed successfully");
        }

        return $this->sendBadRequest("Bad Request");
    } catch (NotFoundHttpException $ex) {
        return $this->notFoundRequest($ex);
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}
// public function addItems(Request $request)
// {
//     try {
//         $inputs = $request->all();

//         // Validation rules
//         $rules = [
//             'name' => 'required|string|max:255',
//             'description' => 'nullable|string',
//             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Assuming image upload
//         ];

//         // Validate request
//         $validator = Validator::make($inputs, $rules);
//         if ($validator->fails()) {
//             return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
//         }

//         // Create category data
//         $category = new Category();
//         $createData = $category->prepareCreateData($inputs);
// // print_r($createData);die;
//         // Create category
//         $category = Category::create($createData);

//         if ($category) {
//             return response()->json(['category' => $category, 'message' => 'Category added successfully'], 200); // 201 Created
//         }

//         return response()->json(['error' => 'Failed to add category'], 500); // 500 Internal Server Error
//     } catch (NotFoundHttpException $ex) {
//         return response()->json(['error' => $ex->getMessage()], 404); // 404 Not Found
//     } catch (Exception $ex) {
//         return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
//     }
// }

public function addItems(Request $request)
{
    try {
        $inputs = $request->all();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
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
        // Create category
        $categoryData = [
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'slug'=> $slug,
            'image' => $imageName  // Return the full URL
        ];

        $category = Category::create($categoryData);

        if ($category) {
            return response()->json(['category' => $category, 'message' => 'Category added successfully'], 201); // 201 Created
        }

        return response()->json(['error' => 'Failed to add category'], 500); // 500 Internal Server Error
    } catch (NotFoundHttpException $ex) {
        return response()->json(['error' => $ex->getMessage()], 404); // 404 Not Found
    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
    }
}public function deleteCategories(Request $request)
{
    $categoryIds = $request->input('categoryIds');

    if (empty($categoryIds) || !is_array($categoryIds)) {
        return response()->json(['error' => 'Invalid Category IDs provided.'], 400);
    }

    try {
        $categories = Category::whereIn('id', $categoryIds)->get();

        if ($categories ->isEmpty()) {
            return response()->json(['error' => 'No valid category found.'], 404);
        }

        foreach ($categories as $category) {
          
            // Delete associated files
            $category->deleteFiles();

            // Delete the company record
            $category->delete();
        }

        return response()->json(['success' => 'Selected categories  deleted successfully.'], 200);
    } catch (\Exception $ex) {
        \Log::error("Error deleting companies: " . $ex->getMessage());
        return response()->json(['error' => $ex->getMessage()], 500);
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
    $filePath = "category/" . $fileName;

    // Save the image to the specified disk (public)
    Storage::disk('public')->put($filePath, base64_decode($image));

    // Return the file name
    return $fileName;
}

public function editCategory(Request $request, $id)
{
    try {
        // Retrieve the category by ID
        $category = Category::findOrFail($id);
        if(!$category){
            return response()->json(['error' => 'Catgory page not found'], 404);

        }
        // Collect all inputs
        $inputs = $request->all();

        // Validation rules
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|string', // Accept base64 string
        ];

        // Validate request
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
        }

        // Decode and save image if provided
        if ($request->has('image') && $request->image !== null && $request->image !== "") {
            $imageName = $this->base64ToImage($request->input('image'));

            // Delete the old image if it exists
            if ($category->image) {
                Storage::disk('public')->delete('category/' . $category->image);
            }

            // Update the image name in the database
            $category->image = $imageName;
        }
        else {
            // No new image provided, retain the old image
            $inputs['image'] = $category->image;
        }

        // Update other fields if provided
        $category->name = $inputs['name'] ?? $category->name;
        $category->description = $inputs['description'] ?? $category->description;
        $category->slug = Str::slug($inputs['name'] ?? $category->name);

        // Save the changes
        $category->save();

        return response()->json(['category' => $category, 'message' => 'Category updated successfully'], 200); // 200 OK
    } catch (NotFoundHttpException $ex) {
        return response()->json(['error' => 'Category not found'], 404); // 404 Not Found
    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
    }
}
}