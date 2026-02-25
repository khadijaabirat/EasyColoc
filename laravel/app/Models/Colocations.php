<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colocations extends Model
{
    /** @use HasFactory<\Database\Factories\ColocationsFactory> */
    use HasFactory;
    protected $fillable = ['name','owner_id','statut'];
    public function owner(){
        return $this->belongsto(User::class,'owner_id');
    }
    public function members(){
        return $this->belongsToMany(
            User::class,
            'memberships',
            'colocation_id',
            'user_id'
        )->withPivot('role', 'left_at')->withTimestamps();
    }

    public function memberships()
    {
        return $this->hasMany(memberships::class);
    }
}
