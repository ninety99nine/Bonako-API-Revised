<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LocationUser extends Pivot
{
    protected $casts = [
        'permissions' => 'array',
        'default_location' => 'boolean'
    ];

    const CLOSED_ANSWERS = [
        'Yes', 'No', 'Not specified'
    ];
}
