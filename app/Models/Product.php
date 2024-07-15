<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Category;
use Hash;
use Str;
use Storage;
use File;

class Product extends Model
{
    use HasFactory , HasApiTokens , Notifiable;
    protected $table = 'product';
   protected $fillable = ['name', 'description', 'price', 'qty', 'category_id', 'image', 'slug', 'status', 'meta_title', 'meta_description', 'meta_keywords','sku'];
   protected $appends = ['category_name'];
   public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : null;
    }
   public function getImageAttribute($value, $showFullPath = true)
   {
       if (!$value) {
           return config('global.profile_image') . "noimage.png";
       }
   
       if ($showFullPath) {
           if (strpos($value, 'http') === 0) {
               return $value;
           }
   
           return config('global.storage_url') . "/product/" . $value;
       } else {
           return basename($value); // Return only the image name
       }
   }
   
// public function getImageAttribute($value)
//     {
//         return basename($value); // This will return just the file name
//     }
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
   public function category()
   {
       return $this->belongsTo(Category::class, 'category_id', 'id');
   }
   public function deleteFiles()
   {
       $files = ['image'];
   
       foreach ($files as $file) {
           if ($this->$file) {
               Storage::disk('public')->delete('product/' . $file . '/' . $this->$file);
           }
       }
   }
   
}
 ?>