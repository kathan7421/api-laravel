<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Banners extends Model
{
    use HasFactory;
    protected $table= 'banners';
    protected $fillable = ['title', 'button_text', 'priority', 'image', 'slug', 'description','status'];

    public function getImageAttribute($value)
    {
        if (!$value) {
            return config('global.profile_image') . "noimage.png";
        }
        
        if (strpos($value, 'http') === 0) {
            return $value;
        }
        
        return config('global.storage_url') . "/banners/" . $value;
    }

    public function deleteFiles()
    {
        $files = ['image'];
    
        foreach ($files as $file) {
            if ($this->$file) {
                Storage::disk('public')->delete('banners/' . $file . '/' . $this->$file);
            }
        }
    }
    
}
