<?php

namespace App\Http\Controllers\Auth;

use App\Repositories\AuthRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\AccountExistsRequest;
use App\Http\Requests\Auth\AcceptTermsAndConditionsRequest;
use App\Http\Requests\Auth\ShowMobileVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyMobileVerificationCodeRequest;
use App\Http\Requests\Auth\GenerateMobileVerificationCodeRequest;

class AuthController extends Controller
{
    private $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function user()
    {
        return $this->authRepository->user();
    }

    public function tokens()
    {
        return $this->authRepository->tokens();
    }

    public function acceptTermsAndConditions(AcceptTermsAndConditionsRequest $acceptTermsAndConditionsRequest)
    {
        return $this->authRepository->acceptTermsAndConditions($acceptTermsAndConditionsRequest);
    }

    public function login(LoginRequest $loginRequest)
    {
        return $this->authRepository->login($loginRequest);
    }

    public function register(RegisterRequest $registerRequest)
    {
        return response()->json($this->authRepository->register($registerRequest), 201);
    }

    public function accountExists(AccountExistsRequest $accountExistsRequest)
    {
        return $this->authRepository->accountExists($accountExistsRequest);
    }

    public function resetPassword(ResetPasswordRequest $resetPasswordRequest)
    {
        return $this->authRepository->resetPassword($resetPasswordRequest);
    }

    public function showMobileVerificationCode(ShowMobileVerificationCodeRequest $showMobileVerificationCodeRequest)
    {
        return $this->authRepository->showMobileVerificationCode($showMobileVerificationCodeRequest);
    }

    public function verifyMobileVerificationCode(VerifyMobileVerificationCodeRequest $verifyMobileVerificationCodeRequest)
    {
        return $this->authRepository->verifyMobileVerificationCode($verifyMobileVerificationCodeRequest);
    }

    public function generateMobileVerificationCode(GenerateMobileVerificationCodeRequest $generateMobileVerificationCodeRequest)
    {
        return $this->authRepository->generateMobileVerificationCode($generateMobileVerificationCodeRequest);
    }

    public function logout(LogoutRequest $logoutRequest)
    {
        return $this->authRepository->logout($logoutRequest);
    }
}
