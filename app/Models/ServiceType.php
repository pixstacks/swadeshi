<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceType extends Model
{
    use HasFactory, Searchable, HasTranslations;

    protected $guarded   = [];
    public $translatable = ['name', 'description'];

    public function gatNameAttribute()
    {
        return $this->name;
    }

    public function subServices()
    {
        return $this->hasMany(ServiceType::class, 'parent_id', 'id');
    }

    public function GeoFencing()
    {
        return $this->belongsToMany(GeoFencing::class, 'service_type_geo_fencings');
    }

    public function parent()
    {
        return $this->belongsTo(ServiceType::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ServiceType::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function childrenRecursive1()
    {
        return $this->children()->with('childrenRecursive');
    }
}
