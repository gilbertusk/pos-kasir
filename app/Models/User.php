<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $avatar
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 */
class User extends Authenticatable

{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Whether the user has recorded sales or expenses that must be preserved.
     */
    public function hasActivityHistory(): bool
    {
        return $this->transactions()->exists() || $this->expenses()->exists();
    }

    /**
     * Whether this user is the only active owner left in the store.
     */
    public function isLastActiveOwner(): bool
    {
        return $this->isOwner()
            && ! static::where('role', 'owner')->where('is_active', true)->whereKeyNot($this->getKey())->exists();
    }
}
