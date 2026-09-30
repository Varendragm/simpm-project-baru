<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only access to finalized maintenance/damage history stored by SIPPM.
 *
 * This model intentionally uses the separate `sippm` database connection.
 * SIMPM must not create, update, or delete SIPPM records.
 */
class SippmHistory extends Model
{
    protected $connection = 'sippm';

    protected $table = 'laporans';

    protected $guarded = [];

    public function scopeFinalHistory(Builder $query): Builder
    {
        return $query->whereNotNull('final_validated_at');
    }
}
