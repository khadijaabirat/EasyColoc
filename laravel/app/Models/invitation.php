<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'colocation_id',
        'status',
    ];

    public function colocation()
    {
        return $this->belongsTo(Colocations::class);
    }
}
