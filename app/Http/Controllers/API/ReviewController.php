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
            $validatedData = $request->validate([
                'comment' => 'required|string|max:255',
                'company_id' => 'required|exists:company_details,id',
                'rating' => 'required|integer|between:1,5',
                'status' => 'required|integer|in:1,2,3'
            ]);

            $review = Review::create($validatedData);

            return response()->json($review, 201);
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
            return $this->successResponse($review->toArray(),'Status Changed Successfully',200);


        }catch(Exception $ex){
            return $this->sendErrorResponse($ex);
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

            $validatedData = $request->validate([
                'comment' => 'sometimes|required|string|max:255',
                'company_id' => 'sometimes|required|exists:company_details,id',
                'rating' => 'sometimes|required|integer|between:1,5',
                'status' => 'sometimes|required|integer|in:1,2,3'
            ]);

            $review->update($validatedData);

            return response()->json($review, 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
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

    // Get average rating for a company
    public function averageRating($companyId)
    {
        try {
            $averageRating = Review::where('company_id', $companyId)->avg('rating');
            return response()->json(['average_rating' => $averageRating], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
