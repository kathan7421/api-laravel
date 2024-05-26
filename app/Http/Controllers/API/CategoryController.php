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

	

// 	public function listItems(Request $request)
// {
//     try {
//         $per_page = $request->input('per_page', 10);
//         $sortBy = $request->input('sortBy', 'id');
//         $search = $request->input('search', null);
//         $direction = strtoupper($request->input('sortDirection', 'ASC'));

//         $query = Category::query(); // Start building the query
        
//         if ($search) {
//             $query->where('name', 'like', '%' . $search . '%');
//         }

//         $query->orderBy($sortBy, $direction);

//         $categories = $query->paginate($per_page);

//         return $this->successResponse($categories->toArray(), 'List of Categories');
//     } catch (Exception $ex) {
//         return $this->sendErrorResponse($ex);
//     }
// }
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
			$user = User::where('id',$id)->first();
			if(!$user){
				return $this->notFoundRequest("User Details not found");
			}
			return $this->successResponse($user->toArray(),"User details");
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
	public function updateData(Request $request,$id)
	{
		try{

			$users = User::where('id',$id)->first();
			if(!$users)
			{
				return $this->sendBadRequest('Request Not Found');
			}
			$inputs = $request->all();
			$rules = [
				'name'=>'required',
				'email'=>'required',
				'password'=>'required',
			];
			$message =  [

				'name.required'=>'Name Field  Is Required',
				'email.required'=>'Email Field Is Required',
				'password.required'=>'Password Field Is Required',

			];

			$validator = Validator::make($inputs,$rules,$message);
			if($validator->fails())
			{
				return $this->sendBadRequest(implode(',',$validator->errors()->all()));
			}
					// print_r($user);die;
			$preparedata = $users->prepareUpdateData($inputs,$users);
			foreach($preparedata as $key=>$value)
			{
				$users->$key = $value;
			}
				// print_r($users->toArray());die;	
			if($users->save())
			{
				$msg = "Request Updated successfully";
					// print_r($users->toArray());die;
				return $this->successResponse([],$msg);

			}
		}
		catch(NotFoundHttpException $ex) 
		{
			return $this->notFoundRequest($ex);
		}catch(Execption $ex){
			return $this->sendErrorResponse($ex);
		}
	}
	public function deleteItems($id)
{
    try {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendBadRequest('Category Not Found');
        }

        // Check if the image exists before attempting to delete it
        if ($category->image && Storage::disk('public')->exists($category->image)) {
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
            // 'description' => 'required|nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Correct validation rules
        ];

        // Validate request
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
        }

       
        // Create category data
        // $categoryData = [
        //     'name' => $inputs['name'],
        //     'description' => $inputs['description'],
        //     'slug' => $slug,
        //     'image'=>$inputs['image'],
        // ];

        // Handle the uploaded file
        // if ($request->hasFile('image')) {
        //     $file = $request->file('image');
        //     $extension = $file->getClientOriginalExtension();

        //     // Generate a unique filename
        //     $filename = Str::random(10) . '.' . $extension;
        //     $path = 'category/' . $filename;

        //     // Store the image
        //     $file->storeAs('public/category', $filename);
        //     $categoryData['image'] = $filename;
        // }

        // Create category
        $cat = new Category();
        $categoryData = $cat->prepareCreateData($inputs);

        
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
}
public function editCategory(Request $request, $id)
{
    try {
        // Retrieve the category by ID
        $category = Category::findOrFail($id);

        // Collect all inputs
        $inputs = $request->all();

        // Validation rules
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];

        // Validate request
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
        }

        // Prepare the update data
        $data = $category->prepareUpdateData($inputs, $category);

        // Handle the uploaded file if exists
        if ($request->hasFile('image')) {
            // Unlink the old image if it exists
            if ($category->image) {
                Storage::disk('public')->delete('category/' . $category->image);
            }

            // Upload new image
            $file = $request->file('image');
            
            $data['image'] = $category->uploadImage($file);
        }

        // Update the category
        $category->update($data);

        return response()->json(['category' => $category, 'message' => 'Category updated successfully'], 200); // 200 OK
    } catch (NotFoundHttpException $ex) {
        return response()->json(['error' => 'Category not found'], 404); // 404 Not Found
    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
    }
}
}