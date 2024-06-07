<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

use Hash;
use Str;
use Storage;
use File;

class Product extends Model
{
    use HasFactory , HasApiTokens , Notifiable;
    protected $table = 'product';
   protected $fillable = ['name', 'description', 'price', 'qty', 'category_id', 'image', 'slug', 'status', 'meta_title', 'meta_description', 'meta_keywords'];
   public function getImageAttribute($value)
   {
       if (!$value) {
           return config('global.profile_image') . "noimage.png";
       }
       
       if (strpos($value, 'http') === 0) {
           return $value;
       }
       
       return config('global.storage_url') . "/product/" . $value;
   }
   public function prepareCreateData($inputs)
   {
       $data = [];
       $data['name'] = array_key_exists('name', $inputs) ? $inputs['name'] : '';
       $data['description'] = array_key_exists('description', $inputs) ? $inputs['description'] : '';
       $data['price'] = array_key_exists('price', $inputs) ? $inputs['price'] : '';
       $data['qty'] = array_key_exists('qty', $inputs) ? $inputs['qty'] : '';
       $data['category_id'] = array_key_exists('category_id', $inputs) ? $inputs['category_id'] : '';
       

       $data['slug'] = Str::slug($data['name']);
   
       // Check if the 'image' key exists and is not empty
       if (array_key_exists('image', $inputs) && !empty($inputs['image'])) {
           $img = $this->uploadImage($inputs['image']);
           $data['image'] = $img;
       }
   
       return $data;
   }
   public function uploadImage($file)
   {
       $fileExtension = $file->getClientOriginalExtension();
       $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
       Storage::disk('public')->put('product/' . $fileName, File::get($file));
       return $fileName;  // Only return the file name
   }
   

   public function prepareUpdateData($inputs, $category)
   {
       $data = [];
       $data['name'] = array_key_exists('name', $inputs) ? $inputs['name'] : $category->name;
       $data['description'] = array_key_exists('description', $inputs) ? $inputs['description'] : $category->description;
       $data['price'] = array_key_exists('price', $inputs) ? $inputs['price'] : $category->price;
       $data['qty'] = array_key_exists('qty', $inputs) ? $inputs['qty'] : $category->qty;
       $data['category_id'] = array_key_exists('category_id', $inputs) ? $inputs['category_id'] : $category->category_id;


       $data['slug'] = Str::slug($data['name']);
   
       // Add image if it exists in inputs
       if (array_key_exists('image', $inputs) && !empty($inputs['image'])) {
           $data['image'] = $inputs['image'];
       }
   
       return $data;
   }
}
 ?>