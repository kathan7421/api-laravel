<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\Review;
use App\Models\Company;
use Illuminate\Support\Facades\Validator;


class ReviewController extends Controller
{
    use ResponseTrait;

    // List reviews with optional filtering and sorting
    public function listItems(Request $request)
    {
        try {
            $sortBy = $request->input('sortBy', 'id');
            $direction = strtoupper($request->input('sortDirection', 'ASC'));
            $search = $request->input('search', null);

            $query = Review::with('company');

            // Apply search filter if provided
            if ($search) {
                $query->where('comment', 'like', '%' . $search . '%');
            }

            // Apply sorting
            $query->orderBy($sortBy, $direction);

            $reviews = $query->get();

            return $this->successResponse($reviews->toArray(), 'List of Reviews', 200);
        } catch (\Exception $ex) {
            return $this->sendErrorResponse($ex);
        }
    }

    // Create a new review
    public function store(Request $request)
    {
        try {
           $inputs = $request->all();

           $validator = Validator::make($request->all(),  [
            'comment' => 'required|string|max:255',
            'company_id' => 'required|exists:company_details,id',
            'rating' => 'required|integer|between:1,5',
           ]);
          
           if($validator->fails()){
            return response()->json(['error'=>$validator->errors()->all()],400);
           }
           $reviews = Review::create([
            'comment'=>$request->input('comment'),
            'company_id'=>$request->input('company_id'),
            'rating'=>$request->input('rating'),
           ]);

            return response()->json($reviews, 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    public function getByid($id){
        try{
            $review = Review::find($id);
            if (!$review) {
                return response()->json(['message' => 'Review not found'], 404);
            }

            return response()->json(['data'=>$review->toArray(),'message'=>'Review get Successfully',200]);
        }
        catch(Exception $ex){
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    // Update a review
    public function update(Request $request, $id)
    {
        try {
            $review = Review::find($id);
            if (!$review) {
                return response()->json(['message' => 'Review not found'], 404);
            }

            $validator = Validator::make($request->all(),  [
                'comment' => 'required|string|max:255',
                'company_id' => 'required|exists:company_details,id',
                'rating' => 'required|integer|between:1,5',
               ]);
               if($validator->fails()){
                return response()->json(['error'=>$validator->errors()->all()],400);
               }
               $reviewData = [
                'comment' => $request->comment,
                'company_id'=>$request->company_id,
                'rating'=>$request->rating
               ];

            $review->update($reviewData);

            return response()->json(['success' => 'Reviews details updated successfully.'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    //Update Status
    public function updateStatus(Request $request, $id)
    {
        try{
            $review = Review::find($id);
            $inputs = $request->all();
            if(!$review){
                   return response()->sendBadRequest('Review Not Found',404);
            }
            $rules = [
                'status' => 'required|integer|in:1,2,3',
            ];
            $validator = Validator::make($inputs,$rules);
            if($validator->fails()){
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $review->status = $inputs['status'];
            $review->save();
            return response()->json([
                'message' => 'Status Changed Successfully'
            ], 200);


        }catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }
    }
   
    // Delete a review
    public function destroy($id)
    {
        try {
            $review = Review::find($id);
            if (!$review) {
                return response()->json(['message' => 'Review not found'], 404);
            }

            $review->delete();

            return response()->json(['message' => 'Review deleted successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
    public function deleteBulk(Request $request){
        try{
            
            $reviewIds = $request->input('reviewsIds');
            if(empty($reviewIds) || !is_array($reviewIds)) {
                return response()->json(['error' => 'Invalid reviews Ids'], 400);
            }
            $reviews = Review::whereIn('id',$reviewIds)->get();
            if ($reviews->isEmpty()) {
                return response()->json(['error' => 'No valid companies found.'], 404);
            }
            foreach($reviews as $review){
                $review->delete();
            }
            return response()->json(['success' => 'Selected Reviews  Deleted Successfully.'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
        
    }
    // Get average rating for a company
    public function averageRating($companyId)
    {
        try {
            $id = Review::where('company_id',$companyId)->first();
            if(!$id){
                return response()->json(['error'=>'Not Found!'],400);
            }
            $averageRating = Review::where('company_id', $companyId)->avg('rating');
            $formattedAverageRating = number_format((float) $averageRating, 1, '.', '');
            return response()->json(['average_rating' => $formattedAverageRating], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
