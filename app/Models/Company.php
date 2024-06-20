<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory , HasApiTokens , Notifiable;
    protected $table= 'company';
    protected $fillable = ['title', 'content', 'slug','status'];
}
