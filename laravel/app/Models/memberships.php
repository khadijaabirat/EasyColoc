<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class memberships extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'colocation_id',
        'role',
        'active',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'active'    => 'boolean',
        'joined_at' => 'datetime',
        'left_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colocation()
    {
        return $this->belongsTo(Colocations::class);
    }
}
