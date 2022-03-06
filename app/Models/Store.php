<?php

namespace App\Models;

use App\Models\Traits\StoreTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory, StoreTrait;

    const CLOSED_ANSWERS = [
        'Yes', 'No', 'Not specified'
    ];

    const REGISTERED_WITH_CIPA_AS = [
        'Company', 'Business', 'Non profit', 'Not specified'
    ];

    const BANKING_WITH = [
        'Absa', 'BancABC', 'Bank Gaborone', 'Bank of Baroda',
        'Bank of India', 'Botswana Savings Bank', 'First Capital Bank',
        'First National Bank', 'Stanbic Bank Botswana', 'Standard Chartered Bank',
        'Other', 'Not specified'
    ];

    protected $casts = [
        'accepted_golden_rules' => 'boolean'
    ];

    protected $fillable = [
        'name', 'call_to_action', 'registered_with_bank', 'banking_with', 'registered_with_cipa', 'registered_with_cipa_as',
        'company_uin', 'number_of_employees', 'accepted_golden_rules',
    ];

    /**
     * Get the Locations owned by the Store
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
