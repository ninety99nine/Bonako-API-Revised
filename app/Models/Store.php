<?php

namespace App\Models;

use App\Casts\Currency;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends BaseModel
{
    use HasFactory;

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

    const DEFAULT_OFFLINE_MESSAGE = 'We are currently offline';

    protected $casts = [
        'online' => 'boolean',
        'accepted_golden_rules' => 'boolean',
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class
    ];

    protected $fillable = [
        'name', 'currency', 'registered_with_bank', 'banking_with', 'registered_with_cipa', 'registered_with_cipa_as',
        'company_uin', 'number_of_employees', 'accepted_golden_rules', 'online', 'offline_message',
        'user_id'
    ];

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     * Get the Locations owned by the Store
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
