<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileVerification extends Model
{
    use HasFactory;

    const PURPOSE = [
        'Verify Account', 'Verify Delivery', 'Reset Password'
    ];

    protected $fillable = [
        'code', 'mobile_number', 'purpose'
    ];
}
