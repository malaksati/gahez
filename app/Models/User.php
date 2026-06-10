<?php

namespace App\Models;

use App\Models\Concerns\VerifiesWithCode;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, VerifiesWithCode;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'birthdate',
        'email_verified_at',
        'phone_verified_at',
        'role',
        'is_active',
        'is_verified',
        'password',
        'image',
        'referral_code',
        'referred_by_id',
        'wallet',
        'points',
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
            'birthdate' => 'date',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/user_avatar.png')
        );
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (! $user->email && ! $user->phone) {
                throw new \Exception(__('You must enter at least one email or phone'));
            }
            if (
                \Illuminate\Support\Facades\Schema::hasColumn('users', 'referral_code')
                && empty($user->referral_code)
            ) {
                $user->referral_code = Str::random(8);
            }
        });
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }

}
