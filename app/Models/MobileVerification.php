<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileVerification extends BaseModel
{
    use HasFactory;

    const PURPOSE = [
        'Verify Account', 'Verify Delivery', 'Reset Password'
    ];

    protected $casts = [
        'code' => 'encrypted'
    ];

    protected $fillable = [
        'code', 'mobile_number', 'purpose'
    ];
}
