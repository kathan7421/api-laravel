<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Hash;
use Str;
use Storage;
use File;

class Country extends Model
{
    use HasFactory , HasApiTokens , Notifiable;

    protected $table = 'country';
    protected $fillable = ['name', 'image', 'slug','description', 'status', 'meta_title', 'meta_description', 'meta_keywords'];
    public function getImageAttribute($value)
    {
        if (!$value) {
            return config('global.profile_image') . "noimage.png";
        }
        
        if (strpos($value, 'http') === 0) {
            return $value;
        }
        
        return config('global.storage_url') . "/country/" . $value;
    }
    
    public function prepareCreateData($inputs)
    {
        $data = [];
        $data['name'] = array_key_exists('name', $inputs) ? $inputs['name'] : '';
        $data['description'] = array_key_exists('description', $inputs) ? $inputs['description'] : '';
        $data['slug'] = Str::slug($data['name']);
    
        // Check if the 'image' key exists and is not empty
        if (array_key_exists('image', $inputs) && !empty($inputs['image'])) {
            // Pass the base64-encoded image directly to the uploadProfileImage method
            $img = $this->uploadImage($inputs['image']);
            $data['image'] = $img;
        }
    
        return $data;
    }
    public function uploadImage($file)
    {
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
        Storage::disk('public')->put('country/' . $fileName, File::get($file));
        return $fileName;  // Only return the file name
    }
    

    public function prepareUpdateData($inputs, $category)
    {
        $data = [];
        $data['name'] = array_key_exists('name', $inputs) ? $inputs['name'] : $category->name;
        $data['description'] = array_key_exists('description', $inputs) ? $inputs['description'] : $category->description;
        $data['slug'] = Str::slug($data['name']);
    
        // Add image if it exists in inputs
        if (array_key_exists('image', $inputs) && !empty($inputs['image'])) {
            $data['image'] = $inputs['image'];
        }
    
        return $data;
    }
    public function getItems($id){
        try{
            $country = Product::find($id);
            if(!$country){
              return $this->notFoundRequest("Country Not Found");
            }

            $data = $product->toArray();

            return $this->successResponse($data,'Country Details');

        }catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }
    }
    public function deleteFiles()
    {
        $files = ['image'];
    
        foreach ($files as $file) {
            if ($this->$file) {
                Storage::disk('public')->delete('country/' . $file . '/' . $this->$file);
            }
        }
    }
}
