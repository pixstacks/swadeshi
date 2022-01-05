<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequestDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id','user_id',
        'product_type_id',
        'provider_id',
        'comments',
        'image',
        'weight',
        'status',
        'address','otp',
        'latitude','longitude',
        
    ];
    
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function userRequest()
    {
        return $this->belongsTo(UserRequest::class);
    }
}
