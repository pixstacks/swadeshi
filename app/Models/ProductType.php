<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductType extends Model
{
    use HasFactory, Searchable, HasTranslations;
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
    public $translatable = ['name', 'description'];


    /**
     * The ProductType that belong to the request.
     */
    public function trips()
    {
        return $this->hasMany(UserRequestDelivery::class);
    }
}
