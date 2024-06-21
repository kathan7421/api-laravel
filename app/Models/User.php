<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use App\Models\Company;
use Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'status',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function prepareCreateData($inputs)
    {
        $data = [];
        $data['name']= array_key_exists('name',$inputs) ? $inputs['name'] : $user->name;
        $data['email']= array_key_exists('email',$inputs) ? $inputs['email'] : $user->email;
        if (array_key_exists('password', $inputs) && !empty($inputs['password'])) {
            $data['password'] = Hash::make($inputs['password']);
        }
        if (array_key_exists('image', $inputs) && !empty($inputs['image'])) {
            $img = $this->uploadProfileImage($inputs['image']);
            $data['image'] = $img;
            // print_r($data);die;
        }
        return $data;
    }
    public function prepareUpdateData($inputs,$user)
    {
        $data = [];
        $data['name']= array_key_exists('name',$inputs) ? $inputs['name'] : $user->name;
        $data['email']= array_key_exists('email',$inputs) ? $inputs['email'] : $user->email;
        if (array_key_exists('password', $inputs) && !empty($inputs['password'])) {
            $data['password'] = Hash::make($inputs['password']);
        }

        return $data;
    }
    public function uploadProfileImage($image)
    {
        // print_r($image);die;
        $logo  = $image;
        $image_parts = explode(";base64,", $logo);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        // print_r(mixed:value, bool:return=false)
        $image_base64 = base64_decode($image_parts[1]);
        $f_name = uniqid() . '.' . $image_type;
        $file = storage_path('app/public/') . $f_name;
        file_put_contents($file, $image_base64);
        return $f_name;
    }
    public function company()
    {
        return $this->hasOne(Company::class, 'user_id', 'id');
    }
}
