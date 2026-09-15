<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'code', 'name', 'location', 'description', 'status'];

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function toBootstrapArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
