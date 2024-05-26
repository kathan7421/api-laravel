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

class Category extends Model
{
    use HasFactory , HasApiTokens , Notifiable;


    protected $fillable = ['parent_id', 'name', 'image', 'slug', 'description', 'status', 'meta_title', 'meta_description', 'meta_keywords'];
    public function getImageAttribute($value)
    {
        return ($value) ? config('global.storage_url')."/category/".$value : config('global.profile_image')."noimage.png"  ;
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
        Storage::disk('public')->put('category/' . $fileName, File::get($file));
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
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public function uploadProfileImage($image)
    {
        $logo  = $image;
        $image_parts = explode(";base64,", $logo);
        
        // Check if the array doesn't have at least two elements
        if (count($image_parts) < 2) {
            // Log an error or throw an exception
            // For now, let's return a message
            return "Invalid image format. Expected data URI format.";
        }
        
        $image_type_aux = explode("image/", $image_parts[0]);
        
        // Check if the array doesn't have at least two elements
        if (count($image_type_aux) < 2) {
            // Log an error or throw an exception
            // For now, let's return a message
            return "Invalid image type. Could not determine image type.";
        }
        
        $image_type = $image_type_aux[1];
        
        // Check if $image_type is empty or not
        if (empty($image_type)) {
            // Log an error or throw an exception
            // For now, let's return a message
            return "Invalid image type. Image type is empty.";
        }
        
        $image_base64 = base64_decode($image_parts[1]);
        $f_name = uniqid() . '.' . $image_type;
        $file = storage_path('app/public/') . $f_name;
        file_put_contents($file, $image_base64);
        
        return $f_name;
    }
    
    

}
