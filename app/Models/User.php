<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PhoneNumber;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'no_telpon',
        'email',
        'password',
        'role',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
    }

    public function isPelanggan(): bool
    {
        return $this->role === 'pelanggan';
    }

    public function pelanggan()
    {
        return $this->hasOne(Pelanggan::class);
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'teknisi_id');
    }

    public static function findForLogin(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return static::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])
                ->first();
        }

        return static::findByPhone($identifier);
    }

    public static function findByPhone(string $phone): ?self
    {
        return static::query()
            ->where(fn (Builder $query) => PhoneNumber::applyMatchQuery($query, 'users.no_telpon', $phone))
            ->first()
            ?? static::query()
                ->whereHas('pelanggan', fn (Builder $query) => PhoneNumber::applyMatchQuery($query, 'no_wa', $phone))
                ->first();
    }

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
        ];
    }
}
