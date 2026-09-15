<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'username', 'role', 'sub_label', 'avatar',
        'phone', 'department', 'joined_year', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Bentuk ringkas dipakai oleh frontend (USERS[role] pada mockup asli).
     */
    public function toBootstrapArray(): array
    {
        return [
            'name' => $this->name,
            'sub' => $this->sub_label,
            'avatar' => $this->avatar,
        ];
    }
}
