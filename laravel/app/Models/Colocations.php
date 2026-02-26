<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Colocations extends Model
{
    /** @use HasFactory<\Database\Factories\ColocationsFactory> */
    use HasFactory;
    protected $fillable = ['name','statut','owner_id'];

    public function members(){
        return $this->belongsToMany(
        User::class,
        'memberships',
        'colocation_id',
        'user_id'
        )->withPivot('role', 'joined_at', 'left_at')->withTimestamps();
    }
    public function expenses()
    {
        return $this->hasMany(expenses::class);
    }
    public function categories()
    {
        return $this->hasMany(categories::class);
    }
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
    public function settlements()
    {
        return $this->hasMany(settlements::class);
    }
    public function memberships()
    {
        return $this->hasMany(memberships::class);
    }
    public function owner()
    {
        return $this->members()->wherePivot('role', 'owner')->first();
    }

}
