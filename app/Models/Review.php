<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;

class Review extends Model
{
    use HasFactory;
    protected $table = 'review';
    protected $fillable = [
        'company_id', 'comment', 'rating', 'status'
    ];
    protected $appends = ['company_name'];

    
    public function getCompanyNameAttribute()
    {
        return $this->company ? $this->company->name : null;
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    
}
