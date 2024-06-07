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
            $this->adjustPriority($inputs['priority']);
        }

        $inputs['slug'] = Str::slug($inputs['title']);
        $inputs['image'] = $imageName; // Set the image name in the inputs array
        $banner = Banners::create($inputs);

        return response()->json(['banner' => $banner, 'message' => 'Banner added successfully'], 201);
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

    // public function updateItems(Request $request, $id)
    // {
    //     $banner = Banner::find($id);
    //     if (!$banner) {
    //         return response()->json(['error' => 'Banner not found'], 404);
    //     }

    //     $inputs = $request->all();

    //     $rules = [
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'button_text' => 'nullable|string|max:255',
    //         'priority' => 'nullable|integer',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //     ];

    //     $validator = Validator::make($inputs, $rules);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()->all()], 400);
    //     }

    //     if ($request->hasFile('image')) {
    //         $imageName = time().'.'.$request->image->extension();  
    //         $request->image->move(public_path('images'), $imageName);
    //         $inputs['image'] = $imageName;
    //     }

    //     if (isset($inputs['priority']) && $inputs['priority'] != $banner->priority) {
    //         $this->adjustPriority($inputs['priority']);
    //     }

    //     $inputs['slug'] = Str::slug($inputs['title']);
    //     $banner->update($inputs);

    //     return response()->json(['banner' => $banner, 'message' => 'Banner updated successfully'], 200);
    // }

    private function adjustPriority($newPriority)
    {
        $existingBanners = Banners::where('priority', '>=', $newPriority)->orderBy('priority', 'asc')->get();
        foreach ($existingBanners as $existingBanner) {
            $existingBanner->update(['priority' => $existingBanner->priority + 1]);
        }
    }
   
}
