<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\User\CreateUserRequest;
use App\Http\Requests\Models\User\UpdateUserRequest;
use App\Http\Requests\Auth\AcceptTermsAndConditionsRequest;
use App\Http\Requests\Auth\ShowMobileVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyMobileVerificationCodeRequest;
use App\Http\Requests\Auth\GenerateMobileVerificationCodeRequest;

class UserController extends BaseController
{
    /**
     *  @var UserRepository
     */
    protected $repository;

    public function index(Request $request)
    {
        return response($this->repository->get()->transform(), 200);
    }

    public function createProfile(CreateUserRequest $request)
    {
        return response($this->repository->createProfile($request), 201);
    }

    public function showProfile(User $user)
    {
        return response($this->repository->setModel($user)->showProfile(), 200);
    }

    public function updateProfile(UpdateUserRequest $request, User $user)
    {
        return response($this->repository->setModel($user)->updateProfile($request)->transform(), 200);
    }

    public function confirmDeleteProfile(User $user)
    {
        return response($this->repository->setModel($user)->confirmDeleteProfile(), 200);
    }

    public function deleteProfile(DeleteRequest $request, User $user)
    {
        return response($this->repository->setModel($user)->deleteProfile(), 204);
    }

    public function showProfileTokens(User $user)
    {
        return response($this->repository->setModel($user)->showProfileTokens(), 200);
    }

    public function acceptTermsAndConditions(AcceptTermsAndConditionsRequest $acceptTermsAndConditionsRequest, User $user)
    {
        return response($this->repository->setModel($user)->acceptTermsAndConditions($acceptTermsAndConditionsRequest), 200);
    }

    public function showMobileVerificationCode(ShowMobileVerificationCodeRequest $showMobileVerificationCodeRequest, User $user)
    {
        return response($this->repository->setModel($user)->showMobileVerificationCode($showMobileVerificationCodeRequest), 200);
    }

    public function verifyMobileVerificationCode(VerifyMobileVerificationCodeRequest $verifyMobileVerificationCodeRequest, User $user)
    {
        return response($this->repository->setModel($user)->verifyMobileVerificationCode($verifyMobileVerificationCodeRequest), 200);
    }

    public function generateMobileVerificationCode(GenerateMobileVerificationCodeRequest $generateMobileVerificationCodeRequest, User $user)
    {
        return response($this->repository->setModel($user)->generateMobileVerificationCode($generateMobileVerificationCodeRequest), 200);
    }

    public function logout(LogoutRequest $logoutRequest, User $user)
    {
        return response($this->repository->setModel($user)->logout($logoutRequest), 200);
    }
}
