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
            'description' => 'nullable|string',
            // 'image' => 'nullable|string', // We expect Base64-encoded image data
        ];

        // Validate request
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400); // 400 Bad Request
        }
        $slug = Str::slug($inputs['name']);
        // Create category data
        $categoryData = [
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'slug'=>$slug,
        ];

        

        // Get the base64 string
        $imageData = $request->input('image');
// print_r($imageData);die;
        // Check if the base64 string contains the image type
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $data = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif, etc.

            // Check if file type is valid
            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                return response()->json(['success' => false, 'message' => 'Invalid image type'], 400);
            }

            $data = base64_decode($data);

            if ($data === false) {
                return response()->json(['success' => false, 'message' => 'Base64 decode failed'], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid base64 string'], 400);
        }

        // Generate a unique filename
        $filename = Str::random(10) . '.' . $type;
        $path = 'images/' . $filename;

        // Store the image
        Storage::disk('public')->put($path, $data);
        $categoryData['image']=$filename;
        


        // Check if image data is provided
        // if (isset($inputs['image'])) {
        //     // Decode Base64-encoded image data
           
        //     $imageData = $inputs['image'];
        //     $imageData = str_replace('data:image/png;base64,', '', $imageData);
        //     $imageData = str_replace(' ', '+', $imageData);
        //     $imageData = base64_decode($imageData);

        //     // Generate a unique filename for the image
        //     $imageName = uniqid() . '.png';

        //     // Store the image file in the storage directory
        //     $imagePath = storage_path('app/public/') . $imageName;
        //     file_put_contents($imagePath, $imageData);

        //     // Add image path to category data
        //     $categoryData['image'] = $imageName;
        // }

        

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $filename = time() . '.' . $image->getClientOriginalExtension();
        //     $path = $image->storeAs('images', $filename, 'public');

        //     return response()->json([
        //         'success' => true,
        //         'path' => $path,
        //         'filename' => $filename,
        //     ]);
        // }


        


        // Create category
        $category = Category::create($categoryData);

        if ($category) {
            return response()->json(['category' => $category, 'message' => 'Category added successfully'], 200); // 201 Created
        }

        return response()->json(['error' => 'Failed to add category'], 500); // 500 Internal Server Error
    } catch (NotFoundHttpException $ex) {
        return response()->json(['error' => $ex->getMessage()], 404); // 404 Not Found
    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500); // 500 Internal Server Error
    }
}

}


	
