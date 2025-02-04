<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\MembershipLevelEnum;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable;
    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'dob',
        'address',
        'phone',
        'email',
        'password',
        'avatar',
        'role',
        'membership_level'
    ];

    protected $casts = [
        'role' => RoleEnum::class,
        'membership_level' => MembershipLevelEnum::class,
    ];

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class, 'user_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::Admin->value;
    }

    public function hasRole(string $role): bool
    {
        try {
            $roleEnum = RoleEnum::fromValue($role);
            return $this->role->value === $roleEnum->value;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    // Implement the JWTSubject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }



    public function borrowTransactions()
    {
        return $this->hasMany(BorrowTransaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    public function membership()
    {
        return $this->hasOne(Membership::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}