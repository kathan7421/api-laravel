<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Models\User;
use App\Traits\ResponseTrait;


class CompanyController extends Controller
{
    use ResponseTrait;
    public function listItems(Request $request)
    {
        try{

        $sortBy = $request->input('sortBy', 'id');
        $search = $request->input('search', null);
        $direction = strtoupper($request->input('sortDirection', 'ASC'));

        $query = User::with('company')->where('user_type', 3);

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $query->orderBy($sortBy, $direction);
        $companies = $query->get();

        return response()->json(['companies' => $companies, 'List of Companies for user_type = 3'], 200);
    }catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
    }
}