<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class categories extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'colocation_id'];

    public function colocation()
    {
        return $this->belongsTo(Colocations::class);
    }

    public function expenses()
    {
        return $this->hasMany(expenses::class, 'category_id');
    }
}
