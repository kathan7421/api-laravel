<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;

class Orders extends Model
{
    use HasFactory , HasApiTokens , Notifiable;
    protected $table = 'order';
    protected $fillable = ['user_id', 'order_date', 'status', 'order_number', 'sub_total', 'shipping_id', 'coupon', 'total_amount', 'quantity', 'payment_method', 'payment_status', 'first_name', 'last_name', 'email', 'phone', 'country', 'post_code', 'address1', 'address2'];
}
