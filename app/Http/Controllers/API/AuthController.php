<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;



class AuthController extends Controller
{

	use ResponseTrait;

	public function login(Request $request)
{
    try {
        $inputs = $request->all();
        $rules = [
            'email' => 'required',
            'password' => 'required|min:5',
        ];
        $messages = [
            'email.required' => 'Email field is required',
            'password.required' => 'Password field is required',
            'password.min' => 'Password must be at least 5 characters long',
        ];

        $validator = Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || $user->user_type !== '1') {
            return response()->json(['error' => 'Invalid email/password'], 404);
        }

        if (Hash::check($inputs['password'], $user->password)) {
            $token = $user->createToken('user')->accessToken;
            $user->token = 'Bearer ' . $token;
            return response()->json(['user' => $user, 'message' => 'Login successful'], 200);
        } else {
            return response()->json(['error' => 'Invalid email/password'], 401);
        }
    } catch (\Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}
public function register(Request $request){
    try{
        $inputs = $request->all();
        $rules = [
            'name'=>'required|min:2',
            'email'=>'required|unique:users,email,null,id',
            'password' => 'sometimes|required',
        ];
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400); // Updated response code to 400 Bad Request
        }

        $user = new User();
        $createData = $user->prepareCreateData($inputs);
        $users = User::create($createData);

        if($users)
        {
            $token = $users->createToken('user')->accessToken;
            $users->token ="Bearer ". $token;
            return response()->json(['user' => $users, 'message' => 'User registered successfully'], 201); // Updated response code to 201 Created
        }
        return response()->json(['error' => 'Failed to register user'], 500); // Updated response code to 500 Internal Server Error
    } catch (NotFoundHttpException $ex) {
        return response()->json(['error' => $ex->getMessage()], 404); // Updated response code to 404 Not Found
    } catch(Exception $ex){
        return response()->json(['error' => $ex->getMessage()], 500); // Updated response code to 500 Internal Server Error
    }
}

	public function listItems(Request $request)
	{
		try
		{

			$user_type = request('user_type','2');
			$per_page = request('per_page',10);
			$sortBy =  request('sortBy','id');
			$search = request('search',null);
			$userc = auth()->user();
			// echo"<pre>";
			// print_r($userc->toArray());die;

			$direction = strtoupper(request('sortDirection','ASC'));

			$user = User::where('status','1')->where('id','!=',$userc->id);

			if($user_type)
			{
				$user->where('user_type',$user_type);
			}
			if($search)
			{
				$user->where('name','like','%'.$search.'%');
			}
			$user->orderBy($sortBy,$direction);

			$userData = $user->paginate($per_page);

			if($userData)
			{
				return $this->successResponse($userData->toArray(),'List Of Users');

			}
			return $this->successResponse([],'List Of Users');
		}catch(Exception $ex)
		{
			return $this->sendErrorResponse($ex);
		}

	}


	public function getItems($id)
	{
		// print_r(Auth()->user()->toArray());die;
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
		try{
			
			$user = User::where('id',$id)->first();

			$image = storage_path('app\public')."/".$user->image;
			// print_r($image);die;
			 @unlink($image);die; // then delete previous photo

			 if(!$user){
			 	return $this->sendBadRequest('User Not Found');
			 }
			 if($user->delete()){

			 	return $this->successResponse([],"User Removed successfully");
			 }

			 return $this->sendBadRequest("Bed Request");
			}catch (NotFoundHttpException $ex) {
				return $this->notFoundRequest($ex);
			}catch(Exception $ex){
				return $this->sendErrorResponse($ex);
			}
		}

	}
