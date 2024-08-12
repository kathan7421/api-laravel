<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company_details';

    protected $fillable = [
        'user_id', 'name', 'description', 'fax', 'email', 'phone', 'website',
        'address', 'logo', 'cover_photo', 'country', 'city', 'register_number',
        'gst_number', 'state', 'status', 'document', 'slug', 'is_active','tag_line'
    ];
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class, 'company_id');
    }
    public function averageRating()
{
    return $this->reviews()->avg('rating');
}
    public function getLogoAttribute($value)
    {
        if (!$value) {
            return config('global.profile_image') . "noimage.png";
        }
        
        if (strpos($value, 'http') === 0) {
            return $value;
        }
        
        return config('global.public_url') . "/uploads/logos/" . $value;
    }
    public function getCoverPhotoAttribute($value)
    {
        if (!$value) {
            return config('global.profile_image') . "noimage.png";
        }
        
        if (strpos($value, 'http') === 0) {
            return $value;
        }
        
        return config('global.public_url') . "/uploads/cover_photos/" . $value;
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function prepareCreateData($inputs)
    {
        $data = [];
        $data['name'] = $inputs['name'] ?? '';
        $data['description'] = $inputs['description'] ?? '';
        $data['fax'] = $inputs['fax'] ?? '';
        $data['phone'] = $inputs['phone'] ?? '';
        $data['email'] = $inputs['email'] ?? '';
        $data['website'] = $inputs['website'] ?? '';
        $data['address'] = $inputs['address'] ?? '';
        $data['country'] = $inputs['country'] ?? '';
        $data['city'] = $inputs['city'] ?? '';
        $data['register_number'] = $inputs['register_number'] ?? '';
        $data['gst_number'] = $inputs['gst_number'] ?? '';
        $data['state'] = $inputs['state'] ?? '';
        $data['is_active'] = $inputs['is_active'] ?? '';
        $data['tag_line'] = $inputs['tag_line'] ?? '';
        

        $data['slug'] = Str::slug($data['name']);

        if (isset($inputs['logo']) && $inputs['logo']->isValid()) {
            $data['logo'] = $this->uploadFile($inputs['logo'], 'company/logos');
        }
        if (isset($inputs['cover_photo']) && $inputs['cover_photo']->isValid()) {
            $data['cover_photo'] = $this->uploadFile($inputs['cover_photo'], 'company/cover_photos');
        }
        if (isset($inputs['document']) && $inputs['document']->isValid()) {
            $data['document'] = $this->uploadFile($inputs['document'], 'company/documents');
        }

        return $data;
    }
    public function prepareUpdateData($inputs, $company)
    {
        $data = [];
        $data['name'] = array_key_exists('name', $inputs) ? $inputs['name'] : $company->name;
        $data['description'] = array_key_exists('description', $inputs) ? $inputs['description'] : $company->description;
        $data['fax'] = array_key_exists('fax', $inputs) ? $inputs['fax'] : $company->fax;
        $data['phone'] = array_key_exists('phone', $inputs) ? $inputs['phone'] : $company->phone;
        $data['email'] = array_key_exists('email', $inputs) ? $inputs['email'] : $company->email;
        $data['website'] = array_key_exists('website', $inputs) ? $inputs['website'] : $company->website;
        $data['address'] = array_key_exists('address', $inputs) ? $inputs['address'] : $company->address;
        $data['country'] = array_key_exists('country', $inputs) ? $inputs['country'] : $company->country;
        $data['city'] = array_key_exists('city', $inputs) ? $inputs['city'] : $company->city;
        $data['register_number'] = array_key_exists('register_number', $inputs) ? $inputs['register_number'] : $company->register_number;
        $data['gst_number'] = array_key_exists('gst_number', $inputs) ? $inputs['gst_number'] : $company->gst_number;
        $data['state'] = array_key_exists('state', $inputs) ? $inputs['state'] : $company->state;
        $data['is_active'] = array_key_exists('is_active', $inputs) ? $inputs['is_active'] : $company->is_active;
        $data['tag_line'] = array_key_exists('tag_line', $inputs) ? $inputs['tag_line'] : $company->tag_line;


        $data['slug'] = Str::slug($data['name']);

        if (isset($inputs['logo']) && $inputs['logo']->isValid()) {
            $data['logo'] = $this->uploadFile($inputs['logo'], 'company/logos');
        }
        if (isset($inputs['cover_photo']) && $inputs['cover_photo']->isValid()) {
            $data['cover_photo'] = $this->uploadFile($inputs['cover_photo'], 'company/cover_photos');
        }
        if (isset($inputs['document']) && $inputs['document']->isValid()) {
            $data['document'] = $this->uploadFile($inputs['document'], 'company/documents');
        }

        return $data;
    }

    public static function uploadFile($file, $directory)
    {
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
        Storage::disk('public')->put($directory . '/' . $fileName, File::get($file));
        return $fileName;
    }
    public function deleteFiles()
{
    $files = ['logo', 'cover_photo', 'document'];

    foreach ($files as $file) {
        if ($this->$file) {
            Storage::disk('public')->delete('uploads/' . $file . '/' . $this->$file);
        }
    }
}

   
    
   

}
