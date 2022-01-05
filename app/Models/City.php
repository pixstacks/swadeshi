<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $searchableFields = ['*'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function ancestors()
    {
        return $this->hasMany(Ancestor::class);
    }
}
