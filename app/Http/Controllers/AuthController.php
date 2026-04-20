<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ListUsersRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected UserService $userService
    ) {
    }

    public function listUsers(ListUsersRequest $request)
    {
        return $this->respond(
            $this->userService->listUsers($request->validated(), $request->user())
        );
    }

    public function register(RegisterRequest $request)
    {
        return $this->respond(
            $this->authService->register(
                $request->validated(),
                $request->user(),
                $request->file('image_path')
            )
        );
    }

    public function login(LoginRequest $request)
    {
        return $this->respond(
            $this->authService->login($request->validated())
        );
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->respond(
            $this->authService->forgotPassword($request->validated())
        );
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->respond(
            $this->authService->resetPassword($request->validated())
        );
    }

    public function resendVerificationEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return $this->respond(
            $this->authService->resendVerificationEmail($validated['email'])
        );
    }

    public function verifyEmail(Request $request, string $id, string $hash)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('dashboard.login', ['verified' => 'invalid']);
        }

        $user = User::find($id);

        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('dashboard.login', ['verified' => 'invalid']);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            $this->authService->dispatchWelcomeNotificationInBackground($user);
        }

        return redirect()->route('dashboard.login', ['verified' => 'success']);
    }

    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => (string) $request->query('token', ''),
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPasswordWeb(ResetPasswordRequest $request)
    {
        $payload = $this->authService->resetPassword($request->validated());

        if (($payload['status'] ?? false) === true) {
            return redirect()
                ->route('dashboard.login')
                ->with('success', $payload['message'] ?? 'Password has been reset successfully.');
        }

        return back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->with('error', $payload['message'] ?? 'Unable to reset password.');
    }

    public function update(UpdateProfileRequest $request)
    {
        return $this->respond(
            $this->authService->updateProfile(
                $request->validated(),
                $request->user(),
                $request->file('image_path')
            )
        );
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        return $this->respond(
            $this->authService->logout($request->user())
        );
    }

    public function deleteUser(\Illuminate\Http\Request $request)
    {
        return $this->respond(
            $this->authService->deleteCurrentUser($request->user())
        );
    }

    public function banUser($id, \Illuminate\Http\Request $request)
    {
        return $this->respond(
            $this->userService->banUser($id, $request->user())
        );
    }

    public function unbanUser($id, \Illuminate\Http\Request $request)
    {
        return $this->respond(
            $this->userService->unbanUser($id, $request->user())
        );
    }
}
