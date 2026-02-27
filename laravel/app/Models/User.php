<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_banned',
        'reputation_score',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'reputation_score' => 'integer',
        ];
    }

public function ownedColocations()
{
    return $this->colocations()->wherePivot('role', 'owner');
}


 public function memberships()
    {
 return $this->hasMany(memberships::class);
    }


 public function colocations()
    {
        return $this->belongsToMany(Colocations::class, 'memberships', 'user_id', 'colocation_id')
            ->withPivot('role', 'joined_at', 'left_at')->withTimestamps();
    }

    public function expenses()
    {
        return $this->hasMany(expenses::class, 'payer_id');
    }
    public function debts()
    {
        return $this->hasMany(settlements::class, 'debtor_id');
    }
    public function credits()
    {
        return $this->hasMany(settlements::class, 'creditor_id');
    }
 

    public function isGlobalAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
