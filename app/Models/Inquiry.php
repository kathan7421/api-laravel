<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Models\Product;
use App\Models\Company;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes; // Use SoftDeletes trait

    protected $table = 'inquiry';
    protected $fillable = ['company_id', 'name', 'phone', 'email', 'subject', 'message', 'service_id', 'status'];
    protected $dates = ['deleted_at']; // Ensure `deleted_at` is treated as a date

    protected $appends = ['service_name', 'company_name'];

    public function getServiceNameAttribute()
    {
        return $this->service ? $this->service->name : null;
    }

    public function getCompanyNameAttribute()
    {
        return $this->company ? $this->company->name : null;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function service()
    {
        return $this->belongsTo(Product::class, 'service_id');
    }

    // Custom query scopes for filtering records
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at'); // Records without a deleted_at timestamp
    }

    public function scopeDeleted($query)
    {
        return $query->whereNotNull('deleted_at'); // Records with a deleted_at timestamp
    }
}
