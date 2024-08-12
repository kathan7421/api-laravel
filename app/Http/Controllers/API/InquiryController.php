<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\Inquiry;
use App\Models\Company;
use App\Mail\InquiryMail;
use App\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;
use League\Csv\Writer;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use DB;
use App\Events\InquiryAdded;


class InquiryController extends Controller
{
    use ResponseTrait;
 

  

    public function listItems(Request $request)
    {
        try {
            $sortBy = $request->input('sortBy', 'id');
            $search = $request->input('search', null);
            $direction = strtoupper($request->input('sortDirection', 'ASC'));
            $startDate = $request->input('startdate', null);
            $endDate = $request->input('end_date', null);
   
            $query = Inquiry::with('company', 'service')->active(); // Use the active scope
    
            // Apply search filter
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
    
            // Apply date range filter if provided
            if ($startDate && $endDate) {
                $startDate = Carbon::parse($startDate)->startOfDay();
                $endDate = Carbon::parse($endDate)->endOfDay();
                \Log::info("Parsed Start Date: " . $startDate);
                \Log::info("Parsed End Date: " . $endDate);
                $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
    
            // Apply sorting if required
            $query->orderBy($sortBy, $direction);
    
            // Debugging: Log the SQL query and bindingss
            // \Log::info("SQL Query: " . $query->toSql());
            // \Log::info("Bindings: " . json_encode($query->getBindings()));
    
            $inquiries = $query->get();
    
            // Convert statuses to boolean
            $inquiries->transform(function ($inquiry) {
                $inquiry->status = $inquiry->status == '0'; // Convert to boolean
                return $inquiry;
            });
    
            return $this->successResponse($inquiries->toArray(), 'List Of Inquiries', 200);
        } catch (Exception $ex) {
            \Log::error("Exception: " . $ex->getMessage());
            return $this->sendErrorResponse($ex);
        }
    }
    

    public function changeStatus(Request $request, $id)
    {
        try {
            $inquiry = Inquiry::find($id);
    
            if ($inquiry) {
                // Toggle in$inquiry status
                $inquiry->status = $inquiry->status == '0' ? '1' : '0';
                $inquiry->save();
                // Determine the status and return the appropriate boolean value
                $isActive = $inquiry->status == '0';
    
                return response()->json([
                    'message' => 'Inquiry status updated successfully',
                    'active' => $isActive
                ], 200);
            }
    
            return response()->json(['message' => 'Inquiry  not found'], 404);
    
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    public function softDelete($id)
    {
        try {
            $inquiry = Inquiry::find($id);
            if ($inquiry) {
                $inquiry->delete(); // This will set the `deleted_at` column
                return response()->json([
                    'data' => [],
                    'message' => 'Inquiry soft deleted successfully'
                ], 200);
            }
            return response()->json([
                'message' => 'Inquiry Not Found'
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
    
    public function restoreAll(Request $request)
{
    try {
        $inquiryIds = $request->input('inquiry_ids');

        if ($inquiryIds && is_array($inquiryIds)) {
            $inquiries = Inquiry::onlyTrashed()->whereIn('id', $inquiryIds)->get();
        } else {
            $inquiries = Inquiry::onlyTrashed()->get();
        }

        if ($inquiries->isEmpty()) {
            return response()->json(['message' => 'No inquiries found to restore'], 404);
        }

        foreach ($inquiries as $inquiry) {
            $inquiry->restore();
        }

        return response()->json(['message' => 'Inquiries restored successfully'], 200);
    } catch (\Exception $ex) {
        return response()->json(['message' => 'An error occurred', 'error' => $ex->getMessage()], 500);
    }
}

    
    public function getById($id)
    {
    try {
        $inquiry = Inquiry::with('company', 'service')->find($id);
        if ($inquiry) {
            return $this->successResponse($inquiry->toArray(), 'Inquiry found successfully', 200);
        }
        return $this->sendBadRequest('Inquiry Not Found', 404);
    } catch (\Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
    }
    public function export(Request $request)
    {
        try {
            $inquiries = Inquiry::all();
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            
            // Add CSV header
            $csv->insertOne(['Id', 'Name', 'Email', 'Phone', 'Service', 'Company', 'Message', 'Created At']);
            
            // Set data row using foreach
            foreach ($inquiries as $inquiry) {
                $csv->insertOne([
                    $inquiry->id,
                    $inquiry->name,
                    $inquiry->email,
                    $inquiry->phone,
                    $inquiry->service_name,
                    $inquiry->company_name,
                    $inquiry->message,
                    $inquiry->created_at
                ]);
            }
            
            // Set response headers
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="Inquiry.csv"',
            ];
            
            // Return CSV file as download
            return response()->streamDownload(function () use ($csv) {
                echo $csv->getContent();
            }, 'Inquiry.csv', $headers);
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    public function sendInquiries(Request $request)
    {
        try {
            $inputs = $request->all();
    
            $rules = [
                'inquiry_ids' => 'required|array',
                'inquiry_ids.*' => 'exists:inquiry,id', // Update this line
                'company_ids' => 'required|array',
                'company_ids.*' => 'exists:company_details,id',
            ];
    
            $validator = Validator::make($inputs, $rules);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
    
            // Fetch the companies details
            $companies = Company::whereIn('id', $inputs['company_ids'])->get();
    
            foreach ($inputs['inquiry_ids'] as $inquiryId) {
                // Fetch the inquiry details
                $inquiry = Inquiry::find($inquiryId);
                if ($inquiry) {
                    // Send the email to all selected company emails
                    foreach ($companies as $company) {
                        Mail::to($company->email)->send(new InquiryMail($inquiry, $company));
                    }
                } else {
                    // Log or handle the case where the inquiry is not found
                    Log::warning("Inquiry not found: " . $inquiryId);
                }
            }
    
            return response()->json(['message' => 'Inquiries sent successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    public function bulkDelete(Request $request){
        $inquiryIds = $request->input('inquiryIds');
        if(empty($inquiryIds) || !is_array($inquiryIds)){
            return $this->sendBadRequest('Inquiry Ids are required', 400);
        }
        try{
            $inquiry = Inquiry::whereIn('id', $inquiryIds)->get();
            if($inquiry->isEmpty()){
             return $this->notFoundRequest('Not Found Inquiries',404);
            }
            foreach($inquiry as $inq)
            {
                $inq->delete();
            }
            return $this->successResponse([], 'Inquiries deleted successfully', 200);
        }catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }
    }
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'message' => 'required|string',
                'company_name' => 'nullable|string|max:255',
                'service_name' => 'nullable|string|max:255'
            ]);
    
            $inquiry = Inquiry::create($validatedData);
    
            // Dispatch the event
            event(new InquiryAdded($inquiry));
    
            return response()->json($inquiry, 201);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }

}


}

