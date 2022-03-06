<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MobileVerification;
use App\Repositories\BaseRepository;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Services\Api\Ussd\UssdService;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ResetPasswordFailedException;
use App\Exceptions\UpdatePasswordFailedException;
use App\Exceptions\AcceptingTermsAndConditionsFailedException;
use App\Exceptions\MobileVerificationCodeGenerationFailedException;

class AuthRepository extends BaseRepository
{
    protected $modelName = 'user';
    protected $modelClass = User::class;
    protected $resourceClass = UserResource::class;

    /**
     *  Return the current authenticated user
     */
    public function user()
    {
        if( $this->model = request()->user() ) {

            //  Return the current authenticated user
            return $this->transform();

        }
    }

    /**
     *  Return the current authenticated user tokens
     */
    public function tokens()
    {
        //  Get the user tokens
        $tokens = request()->user()->tokens;

        //  Get the user tokens
        $tranformedTokens = collect($tokens)->map(fn($token) => $token->only(['name', 'last_used_at']))->toArray();

        return [
            'tokens' => $tranformedTokens
        ];
    }

    /**
     *  Accept the terms and conditions. This will grant
     *  the user access to consume routes that require
     *  the T&C's to be accepted first.
     */
    public function acceptTermsAndConditions(Request $request)
    {
        //  If the user has not accepted the terms and conditions
        if( request()->user()->accepted_terms_and_conditions == false ) {

            //  Accept the terms and conditions
            $accepted = request()->user()->update([
                'accepted_terms_and_conditions' => true
            ]);

            //  If accepted successfully
            if( $accepted ){

                return ['message' => 'Terms and conditions accepted successfully'];

            }else{

                //  Throw an Exception - Failed to accept
                throw new AcceptingTermsAndConditionsFailedException('Failed to accept the terms and conditions');
            }

        }else{

            //  Throw an Exception - Already accepted
            throw new AcceptingTermsAndConditionsFailedException('The terms and conditions have already been accepted');

        }
    }

    /**
     *  Login using the mobile number and password and
     *  return the user account and access token
     *
     *  or ...
     *
     *  Set a new password for the existing account and
     *  return the existing user account and access token
     */
    public function login(Request $request)
    {
        //  Set matching user
        $this->model = $this->getUserFromMobileNumberOrFail();

        //  If the request is coming from the Ussd server then we do not need to verify the password
        if( resolve(UssdService::class)->verifyIfRequestFromUssdServer() ){

            //  Return account and access token
            return $this->getUserAndAccessToken();

        }else{

            //  Check if the user already has a password
            if( $this->model->password ){

                //  Get request password
                $password = $request->input('password');

                //  Check if we have a matching password for the user account
                if( Hash::check($password, $this->model->password) ) {

                    //  Return account and access token
                    return $this->getUserAndAccessToken();

                }else {

                    //  Throw an Exception - Password does not match
                    throw ValidationException::withMessages(['password' => 'The password provided is incorrect.']);

                }

            //  Otherwise the user must update their account password
            }else{

                $this->updateAccountPassword();

                //  Return account and access token
                return $this->getUserAndAccessToken();

            }

        }
    }

    /**
     *  Register new user account and return the
     *  user account and access token
     */
    public function register(Request $request)
    {
        /**
         *  Sometimes when registering we may include / exclude the password,
         *  depending on who is creating an account. Normally customers on
         *  ussd do not provide a password, however merchants using the
         *  mobile app or web are required to provide their password.
         */
        if( $request->filled('password') ){

            //  Encrypt the password (If provided)
            $request->merge(['password' => $this->getEncryptedRequestPassword()]);

        }

        //  The selected fields are allowed to register an account
        $data = $request->only(['first_name', 'last_name', 'mobile_number', 'password']);

        //  Create new account
        $this->create($data);

        //  Revoke the mobile verification code
        $this->revokeMobileVerificationCode();

        //  Return account and access token
        return $this->getUserAndAccessToken();
    }

    /**
     *  Check if user account exists
     */
    public function accountExists(Request $request)
    {
        $this->model = $this->getUserFromMobileNumber();

        //  Return account and access token
        return [
            'user' => $this->model ? $this->transform(['viewAsGuest' => true]) : null,
            'account_exists' => $this->model ? true : false
        ];
    }

    /**
     *  Reset the account password and return the
     *  user account and access token
     */
    public function resetPassword(Request $request)
    {
        //  Set matching user
        $this->model = $this->getUserFromMobileNumberOrFail();

        try {

            $this->updateAccountPassword();

            //  Return account and access token
            return $this->getUserAndAccessToken();

        } catch (UpdatePasswordFailedException $e) {

            //  Throw an Exception - Account password reset failed
            throw new ResetPasswordFailedException;

        }
    }

    /**
     *  Generate mobile verification code
     */
    public function generateMobileVerificationCode(Request $request)
    {
        $shortcode = resolve(UssdService::class)->getMobileVerificationShortcode();
        $mobileNumber = $request->input('mobile_number');
        $purpose = $request->input('purpose');

        //  Generate random 6 digit number
        $code = rand(100000, 999999);

        //  Update existing or create a new verification code
        $successful = MobileVerification::updateOrCreate(
            ['mobile_number' => $mobileNumber],
            ['code' => $code, 'mobile_number' => $mobileNumber, 'purpose' => $purpose]
        );

        if( $successful ){

            return [
                'message' => 'Dial '.$shortcode.' on '.$mobileNumber.' to view the verfication code'
            ];

        }else{

            //  Throw an Exception - Mobile verification code generation failed
            throw new MobileVerificationCodeGenerationFailedException;

        }
    }

    /**
     *  Verify mobile verification code validity
     */
    public function verifyMobileVerificationCode(Request $request)
    {
        $code = $request->input('verification_code');
        $mobileNumber = $request->input('mobile_number');

        return ['is_valid' => MobileVerification::where('mobile_number', $mobileNumber)->where('code', $code)->exists()];
    }

    /**
     *  Show mobile verification code
     */
    public function showMobileVerificationCode(Request $request)
    {
        $mobileNumber = $request->input('mobile_number');

        //  Get the matching mobile verification
        $mobileVerification = MobileVerification::where('mobile_number', $mobileNumber)->first();

        //  Return the mobile verification with limited information
        $data = collect($mobileVerification)->only(['code', 'purpose', 'mobile_number'])->toArray();

        return [
            'exists' => !empty($data),
            'data' => $data
        ];
    }

    /**
     *  Logout authenticated user
     */
    public function logout(Request $request)
    {
        //  If we want to logout from all devices
        if( $request->filled('everyone') && in_array($request->input('everyone'), [true, 'true', 1, '1'])) {

            // Revoke all tokens (Including current token)
            $request->user()->tokens()->delete();

        //  If we want to logout other devices except the current
        }elseif( $request->filled('others') && in_array($request->input('others'), [true, 'true', 1, '1'])) {

            // Revoke all tokens (Except the current token)
            $request->user()->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        }else{

            //  Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();

        }

        return [
            'message' => 'Logged out successfully'
        ];
    }

    /**
     *  Update the account password using the password
     *  provided on the request body
     */
    private function updateAccountPassword()
    {
        //  The selected fields are allowed to update account password
        $data = [

            //  Encrypt the password
            'password' => $this->getEncryptedRequestPassword(),

            //  Set the mobile number verification datetime
            'mobile_number_verified_at' => Carbon::now()

        ];

        if( $this->model->update($data) ) {

            //  Revoke the mobile verification code
            $this->revokeMobileVerificationCode();

        }else{

            //  Throw an Exception - Update account password failed
            throw new UpdatePasswordFailedException;

        }
    }

    /**
     *  Get and encrypt the request password
     */
    private function getEncryptedRequestPassword()
    {
        return bcrypt(request()->input('password'));
    }

    /**
     *  Reset the mobile verification code so that
     *  the same code cannot be used again
     */
    private function revokeMobileVerificationCode()
    {
        $hasProvidedMobileNumber = request()->filled('mobile_number');
        $hasProvidedVerificationCode = request()->filled('verification_code');

        /**
         *  If we provided a mobile verification code to confirm our ownership
         *  of the mobile numbser used to create a new account, then we must
         *  revoke the code so that it cannot be used again.
         */
        if( $hasProvidedMobileNumber && $hasProvidedVerificationCode ){

            $mobileNumber = request()->input('mobile_number');

            //  Revoke the mobile verificaiton code
            MobileVerification::where('mobile_number', $mobileNumber)->update(['code' => null]);

        }
    }

    /**
     *  Get the user and access token response
     */
    private function getUserAndAccessToken()
    {
        return [
            'user' => $this->transform(),
            'access_token' => $this->createAccessToken()
        ];
    }

    /**
     *  Get the user from request mobile number
     */
    private function getUserFromMobileNumber()
    {
        $mobileNumber = request()->input('mobile_number');

        //  Check if we have a matching user
        return $this->model->searchMobileNumber($mobileNumber)->first();
    }

    /**
     *  Get the user from request mobile number or fail
     */
    private function getUserFromMobileNumberOrFail()
    {
        if( $user = $this->getUserFromMobileNumber() ) {

            return $user;

        }else{

            $mobileNumber = request()->input('mobile_number');

            //  Throw an Exception - Account does not exist
            throw ValidationException::withMessages(['mobile_number' => 'The account using the mobile number '.$mobileNumber.' does not exist.']);

        }
    }

    /**
     *  Create a new personal access token for the user.
     */
    private function createAccessToken()
    {
        /**
         *  Check if we have the device name provided on the
         *  request e.g "John's Iphone", otherwise use the
         *  current user's name e.g "John Doe"
         */
        $tokenName = (request()->filled('device_name'))
                     ? request()->input('device_name')
                     : $this->model->name;

        return [
            'token' => $this->model->createToken($tokenName)->plainTextToken
        ];
    }

}
