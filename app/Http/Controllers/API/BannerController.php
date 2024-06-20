<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banners;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Traits\ResponseTrait;
use Storage;

class BannerController extends Controller
{
    use ResponseTrait;
    public function listItems(Request $request)
    {
        $sortBy = $request->input('sortBy', 'id');
        $search = $request->input('search', null);
        $direction = strtoupper($request->input('sortDirection', 'ASC'));

        $query = Banners::query();

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $query->orderBy($sortBy, $direction);
        $banners = $query->get();

        return response()->json(['banners' => $banners, 'message' => 'List of Banners'], 200);
    }

    public function addItems(Request $request)
    {
        $inputs = $request->all();
    
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'button_text' => 'nullable|string|max:255',
            'priority' => 'nullable|integer',
            'image' => 'nullable|regex:/^data:image\/(\w+);base64,/',
        ];
    
        $validator = Validator::make($inputs, $rules);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
    
        $imageName = null;
        if ($request->has('image') && $request->input('image')) {
            $imagePath = $this->base64ToImage($request->input('image'));
            $imageName = basename($imagePath);
        }
    
        if (isset($inputs['priority'])) {
            $check = Banners::where('priority', $request->priority)->first();
            if (!empty($check)) {
                $last_priority = Banners::orderBy('priority', 'desc')->first();
                $new_priority = $last_priority['priority'] + 1;
                Banners::where('priority', $request->priority)
                    ->update(['priority' => $new_priority]);
            }
        }
    
        $inputs['slug'] = Str::slug($inputs['title']);
        $inputs['button_text']=  $request->button_text;
        $inputs['description']= $request->description;
        $inputs['image'] = $imageName; // Set the image name in the inputs array
        $banner = Banners::create($inputs);
    
        return response()->json(['banner' => $banner, 'message' => 'Banner added successfully'], 201);
    }
    
    public function updateItems(Request $request, $id)
{
    try {
        $inputs = $request->all();
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'button_text' => 'nullable|string|max:255',
            'priority' => 'nullable|integer',
            'image' => 'nullable|regex:/^data:image\/(\w+);base64,/',
        ];
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        $banner = Banners::findOrFail($id);

        if ($request->has('image') && $request->input('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete("banners/" . $banner->image);
            }
            $imagePath = $this->base64ToImage($request->input('image'));
            $banner->image = basename($imagePath);
        }

        if (isset($inputs['priority'])) {
            $check = Banners::where('priority', $request->priority)->first();
            if (!empty($check)) {
                $last_priority = Banners::orderBy('priority', 'desc')->first();
                $new_priority = $last_priority['priority'] + 1;
                Banners::where('priority', $request->priority)
                    ->update(['priority' => $new_priority]);
            }
            $banner->priority = $inputs['priority'];
        }

        $banner->title = $inputs['title'];
        $banner->description = $inputs['description'];
        $banner->button_text = $inputs['button_text'];
        $banner->slug = Str::slug($inputs['title']);

        $banner->save();

        return response()->json(['banner' => $banner, 'message' => 'Banner updated successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Banner update failed', 'message' => $e->getMessage()], 500);
    }
}

    
    
    private function base64ToImage($base64Image)
    {
        $image = explode('base64,', $base64Image);
        $image = end($image);
        $image = str_replace(' ', '+', $image);
        $file = "banners/" . uniqid() . '.png';

        Storage::disk('public')->put($file, base64_decode($image));

        return $file;
    }

   public function changeStatus(Request $request,$id){
    try{
        $banner = Banners::find($id);
        if($banner){
            $banner->status =  ($banner->status) == '1' ? '0' : '1';
            $banner->save();
            return response()->json(['message'=>'Banner Status Updated',200]);
        }
        return response()->json(['message'=>'Banner Status Not Updated',404]);
    }
    catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
}
public function getItems(Request $request,$id)
{
    try {
        // Find the product by ID
        $banner = Banners::find($id);

        // Check if product exists
        if (!$banner) {
            return $this->notFoundRequest("Banner Details not found");
        }

        // Use the product model's toArray method to include accessor-modified attributes
        // Pass false as the second argument to get only the image name
        $data = $banner->toArray();
        // $data['image'] = $product->getImageAttribute($product->image, false);

        return $this->successResponse($data, "Banner details");
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
}

public function deleteItems($id){
    try{
        $banner = Banners::where('id',$id)->first();
        if(!$banner){
            return $this->sendBadRequest('Banner Not Found');
        }
        if($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete("banners/" . $banner->image);
        }
        if($banner->delete()){
            return $this->successResponse([],'Banner Deleted Successfully',200);
        }
        return $this->sendBadRequest("Bad Request");
    }
    catch (NotFoundHttpException $ex) {
        return $this->notFoundRequest($ex);
    } catch (Exception $ex) {
        return $this->sendErrorResponse($ex);
    }
    }
}
   

