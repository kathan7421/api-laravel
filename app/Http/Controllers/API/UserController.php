<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\ResponseTrait;
use App\Mail\UserPasswordMail;
use Illuminate\Support\Facades\Mail;
use Hash;
class UserController extends Controller
{
    use ResponseTrait;
    public function listItems(Request $request)
    {
        try {
            $sortBy = $request->input('sortBy', 'id');
            $search = $request->input('search', null);
            $direction = strtoupper($request->input('sortDirection', 'ASC'));

            $query = User::where('user_type', 2);

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            $query->orderBy($sortBy, $direction);
            $users = $query->get();

            

            return response()->json(['data' => $users, 'message' => 'List of Users for user_type = 2'], 200);
        } catch (\Exception $ex) {
            return $this->sendErrorResponse($ex);
        }
    }
    public function addItems(Request $request){
        try{
            //  $validator = Validator::make

        }catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }

    }
    public function checkEmailExists(Request $request)
    {
        $email = $request->query('email');
        $userId = $request->query('userId'); // Get the user ID from query parameters
    
        // Check if email exists in the database excluding the current user's email
        $emailExists = User::where('email', $email)
            ->where('id', '!=', $userId) // Exclude current user's email
            ->exists();
    
        return response()->json($emailExists);
    }
    
}
