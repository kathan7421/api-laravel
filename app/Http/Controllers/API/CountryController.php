<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Auth;
use Str;
use Storage;
use Validator;

class CountryController extends Controller
{
    //
    use ResponseTrait;


    public function listItems(Request $request){
        try{
            $sortBy = $request->input('sortBy','id');
            $search = $request->input('search',null); // corrected typo
            $direction = strtoupper($request->input('sortDirection','ASC'));
            $query = Country::query();
    
            if($search){
                $query->where('name','like','%'. $search . '%');
            }
    
            $query->orderBy($sortBy,$direction);
    
            $countries = $query->get(); // corrected variable name
    
            return $this->successResponse( $countries->toArray() ,'Countries fetched successfully'); // return the response
    
        }catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }
    }
    public function getItems(Request $request,$id)
{
    try {
        // Find the product by ID
        $country = Country::find($id);

        // Check if product exists
        if (!$country) {
            return $this->notFoundRequest("country Details not found");
        }

        // Use the product model's toArray method to include accessor-modified attributes
        // Pass false as the second argument to get only the image name
        $data = $country->toArray();
        // $data['image'] = $product->getImageAttribute($product->image, false);

        return $this->successResponse($data, "country details");
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
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
    $filePath = "country/" . $fileName;

    // Save the image to the specified disk (public)
    Storage::disk('public')->put($filePath, base64_decode($image));

    // Return the file name
    return $fileName;
}
public function addItems(Request $request)
{
    try {
        $inputs = $request->all();

        // Define validation rules with 'description' field as nullable
        $rules = [
            'name' => 'required|string|max:50',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
        ];

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        // Decode and save image if provided
        $imageName = null;
        if ($request->has('image') && $request->input('image')) {
            // Decode the base64 image and save it to storage
            $imagePath = $this->base64ToImage($request->input('image'));
            $imageName = basename($imagePath);
        }

        // Generate slug from the country name
        $slug = Str::slug($inputs['name']);

        // Create country data
        $countryData = [
            'name' => $inputs['name'],
            'slug' => $slug,
            'image' => $imageName,
        ];

        // Check if 'description' is provided before adding it to the country data
        if (isset($inputs['description'])) {
            $countryData['description'] = $inputs['description'];
        }

        // Create the country
        $country = Country::create($countryData);

        return response()->json(['message' => 'Country added successfully'], 200);

    } catch (ModelNotFoundException $ex) {
        return response()->json(['error' => 'Country not found'], 404);
    } catch (\Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}

public function updateItems(Request $request, $id) {
    try {
        $inputs = $request->all();

        $rules = [
            'name' => 'required|string|max:50',
            'image' => 'nullable|string',
        ];

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        // Retrieve the country based on the provided ID
        $country = Country::findOrFail($id);

        // Delete old image if a new one is provided
        if ($request->has('image') && $request->input('image') && $country->image) {
            // Delete old image from storage
            Storage::disk('public')->delete("country/" . $country->image);
        }

        // Decode and save image if provided
        $imageName = null;
        if ($request->has('image') && $request->input('image')) {
            $imagePath = $this->base64ToImage($request->input('image'));
            $imageName = basename($imagePath);
        }

        // Update country data
        $country->name = $inputs['name'];
        $country->description = $inputs['description']; // Assuming description is part of the update
        if ($imageName) {
            $country->image = $imageName;
        }
        $country->save();

        return response()->json(['message' => 'Country updated successfully'], 200);
    } catch (ModelNotFoundException $ex) {
        return response()->json(['error' => 'Country not found'], 404);
    } catch (\Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], 500);
    }
}
public function changeStatus(Request $request,$id) {
    try{
        $country = Country::where('id',$id)->first();
        if($country){
            $country->status = ($country->status) == '1' ? '0' : '1';
            $country->save();
            return response()->json(['message'=>'Country Status Updated',200]);

        }
        return response()->json(['message'=>'Country Status Not Updated',404]);
    }
    catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
}
public function deleteItems($id)
{
    try{
        $country = Country::where('id',$id)->first();

        if(!$country){
          return $this->sendBadRequest('Country Not Found');
        }
        if($country->image && Storage::disk('public')->exists($country->image)) {
            Storage::disk('public')->delete($country->image);
        }
    
        if($country->delete()){
            return $this->successResponse([],'Country Deleted Successfully',200);
        }
        return $this->sendBadRequest("Bad Request");
    }

    catch (NotFoundHttpException $ex) {
        return $this->notFoundRequest($ex);
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}
public function deleteAll(Request $request){
    try{
        $ids= $request->input('countryIds');
        if(empty($ids) || !is_array($ids)){
            return $this->sendBadRequest('Invalid Request',400);
        }
        $country = Country::whereIn('id',$ids)->get();
        if($country->isEmpty()){
            return $this->sendBadRequest('Country Not Found',404);
        }
        foreach($country as $item){
            $item->deleteFiles();
            $item->delete();
        }
        return $this->successResponse([],'Country Deleted Successfully',200);
    }catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
}
}