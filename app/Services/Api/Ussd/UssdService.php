<?php

namespace App\Services\Api\Ussd;

class UssdService
{
    /**
     *  Get the main shortcode
     *  @return string
     */
    public function getMainShortcode()
    {
        return '*'.config('app.USSD_MAIN_SHORT_CODE').'';
    }

    /**
     *  Get the mobile verification shortcode
     *  @return string
     */
    public function getMobileVerificationShortcode()
    {
        return '*'.config('app.USSD_MAIN_SHORT_CODE').'*0000#';
    }

    /**
     *  Verify if the incoming request is from the USSD server
     *  @return boolean
     */
    public function verifyIfRequestFromUssdServer()
    {
        return request()->ip() == config('app.USSD_SERVER_IP_ADDRESS');
    }

}
