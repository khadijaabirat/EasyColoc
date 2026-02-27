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
        return $this->hasMany(expenses::class, 'colocation_id');
    }
    public function categories()
    {
        return $this->hasMany(categories::class, 'colocation_id');
    }
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
    public function settlements()
    {
        return $this->hasMany(settlements::class, 'colocation_id');
    }
    public function memberships()
    {
        return $this->hasMany(memberships::class, 'colocation_id');
    }
    public function owner()
    {
        return $this->members()->wherePivot('role', 'owner')->first();
    }

}
