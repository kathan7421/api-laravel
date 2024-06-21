<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

use App\Models\User;

class Company extends Model
{
    use HasFactory , HasApiTokens , Notifiable;
    protected $table= 'company_details';
    protected $fillable = ['title', 'content', 'slug','status'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
