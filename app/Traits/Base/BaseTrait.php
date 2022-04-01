<?php

namespace App\Traits\Base;

use Illuminate\Support\Str;

trait BaseTrait
{
    public $supportedCurrencySymbols = [
        'BWP' => 'P'
    ];

    public function convertToCurrencyFormat($currencyCode = null)
    {
        $symbol = '';
        $currencyCode = $currencyCode ? $currencyCode : $this->currency;

        //  If we have the currency code
        if( $currencyCode ) {

            //  If the currency has a matching symbol
            if( isset( $this->supportedCurrencySymbols[ $currencyCode ] ) ) {

                //  Set the symbol
                $symbol = $this->supportedCurrencySymbols[ $currencyCode ];

            }

        }

        return [
            'symbol' => $symbol,
            'code' => $currencyCode
        ];
    }

    public function convertToMoneyFormat($value = 0, $currencyCode = null)
    {
        $symbol = $this->convertToCurrencyFormat($currencyCode)['symbol'];

        //  Convert value to money format
        $money = number_format($value, 2, '.', ',');

        //  Convert value to float
        $amount = (float) $value;

        return [
            'currency_money' => $symbol . $money,
            'money' => $money,
            'amount' => $amount,
        ];
    }

    public function getResourceType()
    {
        return strtolower(Str::snake(class_basename($this)));
    }
}
