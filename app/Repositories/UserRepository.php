<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = true;

    /**
     *  Return the AuthRepository instance
     *
     *  @return AuthRepository
     */
    public function authRepository()
    {
        return resolve(AuthRepository::class)->setModel(

            /*
             *  Set the current UserRepository instance Model as the
             *  AuthRepository isntance Model so that we are
             *  strictly referencing the user set instead of
             *  the currently authenticated user who may
             *  be the Super Admin performing this
             *  action on behalf of another User.
             */
            $this->model

        );
    }

    /**
     *  Return the user
     */
    public function showProfile()
    {
        return $this->authRepository()->showProfile();
    }

    /**
     *  Return the user tokens
     */
    public function showProfileTokens()
    {
        return $this->authRepository()->showProfileTokens();
    }

    /**
     *  Return the user tokens
     */
    public function acceptTermsAndConditions()
    {
        return $this->authRepository()->acceptTermsAndConditions();
    }

    /**
     *  Register new user account and return the user account
     */
    public function createProfile(Request $request)
    {
        return $this->authRepository()->register($request);
    }

    /**
     *  Update existing user account and return the user account
     */
    public function updateProfile(Request $request)
    {
        return $this->authRepository()->updateProfile($request);
    }

    /**
     *  Confirm delete of the user's profile
     */
    public function confirmDeleteProfile()
    {
        return $this->authRepository()->confirmDeleteProfile();
    }

    /**
     *  Delete the user's profile
     */
    public function deleteProfile()
    {
        return $this->authRepository()->deleteProfile();
    }

    /**
     *  Generate mobile verification code
     */
    public function generateMobileVerificationCode(Request $request)
    {
        //  Set the users mobile number on the request payload
        $request->merge(['mobile_number' => $this->model->mobile_number]);

        return $this->authRepository()->generateMobileVerificationCode($request);
    }

    /**
     *  Verify mobile verification code validity
     */
    public function verifyMobileVerificationCode(Request $request)
    {
        //  Set the users mobile number on the request payload
        $request->merge(['mobile_number' => $this->model->mobile_number]);

        return $this->authRepository()->verifyMobileVerificationCode($request);
    }

    /**
     *  Show mobile verification code
     */
    public function showMobileVerificationCode(Request $request)
    {
        //  Set the users mobile number on the request payload
        $request->merge(['mobile_number' => $this->model->mobile_number]);

        return $this->authRepository()->showMobileVerificationCode($request);
    }

    /**
     *  Logout the user
     */
    public function logout()
    {
        return $this->authRepository()->logout();
    }

}
