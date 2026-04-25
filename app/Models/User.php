<?php

namespace App\Models;

use App\Notifications\Auth\EmailVerificationNotification;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements MustVerifyEmail

{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'type',
        'image_url',
        'address',
        'email',
        'email_verified_at',
        'password',
        'role_id',
        'seller_status',
        'is_banned',
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
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function isSuperAdmin()
    {
        return (int) $this->role_id === 1;
    }

    public function isAdmin()
    {
        return (int) $this->role_id === 1;
    }

    public function isSeller()
    {
        return (int) $this->role_id === 2;
    }

    public function isCustomer()
    {
        return (int) $this->role_id === 3;
    }

    public function resolveSellerStatus(): string
    {
        if ($this->is_banned) {
            return 'banned';
        }

        if ($this->seller_status) {
            return $this->seller_status;
        }

        if ($this->isSeller() && !$this->hasVerifiedEmail()) {
            return 'pending_review';
        }

        return 'approved';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new EmailVerificationNotification());
    }
}
