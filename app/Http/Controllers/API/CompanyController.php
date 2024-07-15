<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Models\User;
use App\Traits\ResponseTrait;
use App\Mail\UserPasswordMail;
use Illuminate\Support\Facades\Mail;
use Hash;


class CompanyController extends Controller
{
   
    public function getCount()
    {
        try {
            $companyCount = Company::count();
            return response()->json(['count' => $companyCount, 'message' => 'Company Count'], 200);
        } catch (\Exception $ex) {
            return $this->sendErrorResponse($ex);
        }
    }
    // public function listItems(Request $request)
    // {
    //     try{

    //     $sortBy = $request->input('sortBy', 'id');
    //     $search = $request->input('search', null);
    //     $direction = strtoupper($request->input('sortDirection', 'ASC'));

    //     $query = User::with('company')->where('user_type', 3);

    //     if ($search) {
    //         $query->where('name', 'like', '%' . $search . '%');
    //     }

    //     $query->orderBy($sortBy, $direction);
    //     $companies = $query->get();

    //     return response()->json(['data' => $companies, 'List of Companies for user_type = 3'], 200);
    // }catch(Exception $ex){
    //     return $this->sendErrorResponse($ex);
    // }
    // } 

    public function listItems(Request $request)
    {
        try {
            $sortBy = $request->input('sortBy', 'id');
            $search = $request->input('search', null);
            $direction = strtoupper($request->input('sortDirection', 'ASC'));

            $query = User::with('company')->where('user_type', 3);

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            $query->orderBy($sortBy, $direction);
            $users = $query->get();

            // Extract companies from users and format the response
            $companies = $users->map(function ($user) {
                return $user->company;
            });

            return response()->json(['data' => $companies, 'message' => 'List of Companies for user_type = 3'], 200);
        } catch (\Exception $ex) {
            return $this->sendErrorResponse($ex);
        }
    }
    public function addItems(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string',
                'address' => 'required|string',
                'fax' => 'nullable|string',
                'password' => 'required|string|min:6',
                'logo_base64' => 'nullable|string', // New base64 field for logo
                'cover_photo_base64' => 'nullable|string', // New base64 field for cover photo
                'document_base64' => 'nullable|string', // New base64 field for document
                'website' => 'required|string',
                'description' => 'nullable|string',
                'gst_number' => 'nullable|string',
                'country' => 'required|string',
                'state' => 'required|string',
                'city' => 'required|string',
                'register_number' => 'nullable|string',
                'tag_line' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
    
            // Handle base64 encoded images
            $logoFileName = null;
            $coverPhotoFileName = null;
            $documentFileName = null;
    
            if ($request->has('logo')) {
                $logoBase64 = $request->input('logo');
                $logoFileName = $this->saveBase64File($logoBase64, 'logos');
            }
    
            if ($request->has('cover_photo')) {
                $coverPhotoBase64 = $request->input('cover_photo');
                $coverPhotoFileName = $this->saveBase64File($coverPhotoBase64, 'cover_photos');
            }
    
            if ($request->has('document')) {
                $documentBase64 = $request->input('document');
                $documentFileName = $this->saveBase64File($documentBase64, 'documents');
            }
    
            // Create User
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'phone' => $request->input('phone'),
                'user_type' => 3,
                'image' => $logoFileName, // Assuming 'image' field in User model
            ]);
    
            // Create Company
            $company = new Company();
            $company->user_id = $user->id;
            $company->name = $request->input('name');
            $company->email = $request->input('email');
            $company->phone = $request->input('phone');
            $company->address = $request->input('address');
            $company->fax = $request->input('fax');
            $company->website = $request->input('website');
            $company->description = $request->input('description');
            $company->gst_number = $request->input('gst_number');
            $company->country = $request->input('country');
            $company->state = $request->input('state');
            $company->city = $request->input('city');
            $company->register_number = $request->input('register_number');
            $company->tag_line = $request->input('tag_line');
            $company->logo = $logoFileName; // Assuming 'logo' field in Company model
            $company->cover_photo = $coverPhotoFileName; // Assuming 'cover_photo' field in Company model
            $company->document = $documentFileName; // Assuming 'document' field in Company model
            $company->save();
    
            return response()->json(['success' => 'Company Details Added successfully.', 'company' => $company], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    
      /**
     * Decode base64 image or document and save it to storage.
     *
     * @param string $base64String
     * @param string $directory
     * @return string|null
     * @throws \Exception
     */
    private function saveBase64File($base64String, $directory)
    {
        // Extract base64 content and determine file extension
        $data = explode('base64,', $base64String);
        $base64 = end($data);
    
        // Determine file extension based on MIME type
        $mime = mime_content_type($base64String);
        switch ($mime) {
            case 'image/jpeg':
            case 'image/png':
                $extension = 'png'; // Adjust based on the actual MIME type
                break;
            case 'application/pdf':
                $extension = 'pdf';
                break;
            case 'application/msword':
                $extension = 'doc';
                break;
            case 'application/vnd.ms-excel':
                $extension = 'xls';
                break;
            default:
                throw new \Exception('Unsupported file type.');
        }
    
        // Decode base64 to binary data
        $file = base64_decode($base64);
    
        // Generate a unique file name
        $fileName = uniqid() . '.' . $extension;
    
        // Specify the file path
        $filePath = 'uploads/' . $directory . '/' . $fileName;
    
        // Save the file to storage
        file_put_contents(public_path($filePath), $file);
    
        // Return the file path
        return $fileName;
    }
    


    public function updateItems(Request $request, $userId)
{
    try {
        $user = User::findOrFail($userId);
        $inputs = $request->all();

        $rules = [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'fax' => 'required|string',
            'logo_base64' => 'nullable|string', // New base64 field for logo
            'cover_photo_base64' => 'nullable|string', // New base64 field for cover photo
            'document_base64' => 'nullable|string', // New base64 field for document
        ];

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        // Find the user and associated company
        $company = $user->company;

        // Update User details
        $user->update([
            'name' => $inputs['name'],
            'email' => $inputs['email'],
            'phone' => $inputs['phone'],
        ]);

        // Prepare data for company update
        $companyData = [
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'phone' => $inputs['phone'],
            'address' => $inputs['address'],
            'fax' => $inputs['fax'],
        ];

        // Handle logo update
        if (!empty($inputs['logo'])) {
            $logoFileName = $this->saveBase64File($inputs['logo'], 'logos');
            $company->deleteFiles($company->logo); // Delete old logo file if exists
            $companyData['logo'] = $logoFileName;
        }

        // Handle cover photo update
        if (!empty($inputs['cover_photo'])) {
            $coverPhotoFileName = $this->saveBase64File($inputs['cover_photo'], 'cover_photos');
            $company->deleteFiles($company->cover_photo); // Delete old cover photo file if exists
            $companyData['cover_photo'] = $coverPhotoFileName;
        }

        // Handle document update
        if (!empty($inputs['document'])) {
            $documentFileName = $this->saveBase64File($inputs['document'], 'documents');
            $company->deleteFiles($company->document); // Delete old document file if exists
            $companyData['document'] = $documentFileName;
        }

        // Update company with the modified data
        $company->update($companyData);

        return response()->json(['success' => 'Company details updated successfully.'], 200);
    } catch (\Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}

    public function deleteCompany($companyId)
{
    try {
        $user = User::where('id', $companyId)->first();
        $company = Company::where('user_id', $companyId)->first();

        if (!$company) {
            return response()->json(['error' => 'Company not found.'], 404);
        }

        // Delete associated user if found
        if ($user) {
            $user->delete();
        }

        // Check if $company exists before trying to delete files
        if ($company) {
            // Delete associated files
            $company->deleteFiles();

            // Delete the company record
            $company->delete();

            return response()->json(['success' => 'Company and associated user deleted successfully.'], 200);
        } else {
            return response()->json(['error' => 'Company not found.'], 404);
        }
    } catch (\Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}

    public function getCompanyById($id)
    {
        try {
            // Find the user by ID
            $user = User::where('id',$id)->first();
            // print_r($user->toArray());die;
            if(!$user){
                return response()->json(['error' => 'Company User not found'], 404);

            }
            // Retrieve the associated company using the user's relationship
            $company = $user->company()->first();

            if (!$company) {
                return response()->json(['error' => 'Company not found'], 404);
            }

            // Return user and company details as JSON response
            return response()->json([
                'user' => $user,
                'company' => $company,
            ], 200);

        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    public function changeStatus(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if ($user) {
                // Toggle user status
                $user->status = $user->status == '1' ? '0' : '1';
                $user->save();

                // Find associated company and toggle its status if exists
                $company = Company::where('user_id',$user->id)->first();
                if ($company) {
                    $company->status = $user->status; // Sync company status with user status
                    $company->save();
                }

                return response()->json(['message' => 'User and associated company status updated successfully'], 200);
            }

            return response()->json(['message' => 'User or associated company not found'], 404);

        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
}
public function deleteCompanies(Request $request)
{
    $companyIds = $request->input('companyIds');

    if (empty($companyIds) || !is_array($companyIds)) {
        return response()->json(['error' => 'Invalid company IDs provided.'], 400);
    }

    try {
        $companies = Company::whereIn('user_id', $companyIds)->get();

        if ($companies->isEmpty()) {
            return response()->json(['error' => 'No valid companies found.'], 404);
        }

        foreach ($companies as $company) {
            $user = $company->user; // Assuming the company model has a relationship with the user model

            // Delete associated user if found
            if ($user) {
                $user->delete();
            }

            // Delete associated files
            $company->deleteFiles();

            // Delete the company record
            $company->delete();
        }

        return response()->json(['success' => 'Selected companies and associated users deleted successfully.'], 200);
    } catch (\Exception $ex) {
        \Log::error("Error deleting companies: " . $ex->getMessage());
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}

public function activeCompany(Request $request, $id)
{
    try {
        // Find the company by user_id
        $company = Company::where('user_id', $id)->first();

        if ($company) {
            // Update company is_active and status to 1
            $company->is_active = 1;
            $company->status = $company->status == '0' ? '1' : '1';
            $company->save();
            
            // Find associated user and update status to 1
            $user = User::find($id);
            if ($user) {
                $user->status = $user->status == '0' ? '1' : '1';

                // Generate a random password
                $password = Str::random(12);
                $user->password = Hash::make($password);
                $user->save();

                // Send email to user with the random password
                $this->sendPasswordEmail($user, $password);

                return response()->json(['message' => 'Company and associated user activated successfully'], 200);
            }
        }

        return response()->json(['message' => 'Company or user not found'], 404);

    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}

private function sendPasswordEmail($user, $password)
{
    // Assuming you have a mailable setup, replace with your actual mailable class
    Mail::to($user->email)->send(new UserPasswordMail($user, $password));
}



}