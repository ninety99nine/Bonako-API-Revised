<?php

namespace App\Http\Controllers\Auth;

use App\Repositories\AuthRepository;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\AccountExistsRequest;
use App\Http\Requests\Models\User\CreateUserRequest;
use App\Http\Requests\Models\User\UpdateUserRequest;
use App\Http\Requests\Auth\AcceptTermsAndConditionsRequest;
use App\Http\Requests\Auth\ShowMobileVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyMobileVerificationCodeRequest;
use App\Http\Requests\Auth\GenerateMobileVerificationCodeRequest;

class AuthController extends BaseController
{
    /**
     *  @var AuthRepository
     */
    protected $repository;

    public function showProfile()
    {
        return response()->json($this->repository->showProfile(), 200);
    }

    public function updateProfile(UpdateUserRequest $updateUserRequest)
    {
        return response()->json($this->repository->updateProfile($updateUserRequest)->transform(), 200);
    }

    public function confirmDeleteProfile()
    {
        return response($this->repository->confirmDeleteProfile(), 200);
    }

    public function deleteProfile()
    {
        return response($this->repository->deleteProfile(), 204);
    }

    public function showProfileTokens()
    {
        return response($this->repository->showProfileTokens(), 200);
    }

    public function acceptTermsAndConditions(AcceptTermsAndConditionsRequest $acceptTermsAndConditionsRequest)
    {
        return response($this->repository->acceptTermsAndConditions($acceptTermsAndConditionsRequest), 200);
    }

    public function login(LoginRequest $loginRequest)
    {
        return response($this->repository->login($loginRequest), 200);
    }

    public function register(CreateUserRequest $createUserRequest)
    {
        return response()->json($this->repository->register($createUserRequest), 201);
    }

    public function accountExists(AccountExistsRequest $accountExistsRequest)
    {
        return response($this->repository->accountExists($accountExistsRequest), 200);
    }

    public function resetPassword(ResetPasswordRequest $resetPasswordRequest)
    {
        return response($this->repository->resetPassword($resetPasswordRequest), 200);
    }

    public function showMobileVerificationCode(ShowMobileVerificationCodeRequest $showMobileVerificationCodeRequest)
    {
        return response($this->repository->showMobileVerificationCode($showMobileVerificationCodeRequest), 200);
    }

    public function verifyMobileVerificationCode(VerifyMobileVerificationCodeRequest $verifyMobileVerificationCodeRequest)
    {
        return response($this->repository->verifyMobileVerificationCode($verifyMobileVerificationCodeRequest), 200);
    }

    public function generateMobileVerificationCode(GenerateMobileVerificationCodeRequest $generateMobileVerificationCodeRequest)
    {
        return response($this->repository->generateMobileVerificationCode($generateMobileVerificationCodeRequest), 200);
    }

    public function logout(LogoutRequest $logoutRequest)
    {
        return response($this->repository->logout($logoutRequest), 200);
    }
}
