<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponseBuilder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $validated, ?User $currentUser, mixed $imageFile = null): array
    {
        $customerRole = Role::where('slug', 'customer')->value('id') ?? 3;
        $imagePath = $this->storeImage($imageFile);
        $requestedRole = $validated['role_id'] ?? null;
        $roleId = (!$currentUser || (int) $currentUser->role_id !== 1)
            ? $customerRole
            : ($requestedRole ?? $customerRole);

        $user = User::create([
            'name' => $validated['name'],
            'role_id' => $roleId,
            'seller_status' => $this->resolveInitialSellerStatus($roleId),
            'image_url' => $imagePath,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => null,
        ]);

        $user->load('role:id,name');

        $this->dispatchRegistrationVerificationInBackground($user);

        return ApiResponseBuilder::success('User registered successfully. Please verify your email address.', $user->makeHidden(['role_id']), 201, [
            'token' => $user->createToken('mytoken')->plainTextToken,
        ]);
    }

    public function login(array $validated): array
    {
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponseBuilder::error('Invalid credentials', 401);
        }

        if ($user->is_banned) {
            $user->tokens()->delete();

            return ApiResponseBuilder::error('This account has been banned.', 403);
        }

        if (!$user->hasVerifiedEmail()) {
            return ApiResponseBuilder::error(
                'Your email address is not verified yet. Please check your inbox and verify your email before signing in.',
                409
            );
        }

        $user->load('role:id,name');

        return ApiResponseBuilder::success('Login successful', $user->makeHidden(['role_id']), 200, [
            'token' => $user->createToken('mytoken')->plainTextToken,
        ]);
    }

    public function updateProfile(array $validated, User $currentUser, mixed $imageFile = null): array
    {
        $imagePath = $imageFile ? $this->storeImage($imageFile, true) : $currentUser->image_url;
        $emailChanged = array_key_exists('email', $validated)
            && $validated['email'] !== $currentUser->email;

        $data = [
            'name' => $validated['name'] ?? $currentUser->name,
            'image_url' => $imagePath,
            'email' => $validated['email'] ?? $currentUser->email,
            'phone' => $validated['phone'] ?? $currentUser->phone,
            'password' => !empty($validated['password']) ? bcrypt($validated['password']) : $currentUser->password,
        ];

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        if (array_key_exists('role_id', $validated) && (int) $currentUser->role_id === 1) {
            $data['role_id'] = $validated['role_id'];
            $data['seller_status'] = $this->resolveInitialSellerStatus((int) $validated['role_id']);
        }

        $currentUser->update($data);
        $currentUser->load('role:id,name');

        if ($emailChanged) {
            try {
                $currentUser->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return ApiResponseBuilder::success('User updated successfully', $currentUser, 200, [
            'token' => $currentUser->createToken('mytoken')->plainTextToken,
        ]);
    }

    public function logout(User $user): array
    {
        $user->currentAccessToken()?->delete();

        return ApiResponseBuilder::success('Logout successful');
    }

    public function deleteCurrentUser(?User $user): array
    {
        if (!$user) {
            return ApiResponseBuilder::error('User not found', 404);
        }

        User::destroy($user->id);

        return ApiResponseBuilder::success('User deleted successfully');
    }

    public function forgotPassword(array $validated): array
    {
        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if (in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            return ApiResponseBuilder::success(
                'If the email exists in our system, a password reset link has been sent.'
            );
        }

        return ApiResponseBuilder::error(__($status), 400);
    }

    public function resetPassword(array $validated): array
    {
        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponseBuilder::success('Password has been reset successfully.');
        }

        return ApiResponseBuilder::error(__($status), 422);
    }

    public function resendVerificationEmail(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || $user->hasVerifiedEmail()) {
            return ApiResponseBuilder::success(
                'If the email exists and is not verified yet, a verification link has been sent.'
            );
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);

            return ApiResponseBuilder::error('Unable to send verification email right now.', 500);
        }

        return ApiResponseBuilder::success(
            'If the email exists and is not verified yet, a verification link has been sent.'
        );
    }

    public function dispatchWelcomeNotificationInBackground(User $user): void
    {
        try {
            $dispatch = fn () => $this->spawnDetachedNotificationProcess($user->id, 'welcome');

            if (DB::transactionLevel() > 0) {
                DB::afterCommit($dispatch);
                return;
            }

            $dispatch();
        } catch (\Throwable $exception) {
            Log::error('Failed to start background welcome notification process.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function dispatchRegistrationVerificationInBackground(User $user): void
    {
        try {
            $dispatch = fn () => $this->spawnDetachedNotificationProcess($user->id, 'verification');

            // If registration is wrapped in a transaction later, only start the background process after commit.
            if (DB::transactionLevel() > 0) {
                DB::afterCommit($dispatch);
                return;
            }

            $dispatch();
        } catch (\Throwable $exception) {
            Log::error('Failed to start background registration verification process.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function spawnDetachedNotificationProcess(int $userId, string $type): void
    {
        $artisan = base_path('artisan');
        $phpBinary = PHP_BINARY;
        $userId = (int) $userId;
        $type = $type === 'welcome' ? 'welcome' : 'verification';

        if (DIRECTORY_SEPARATOR === '\\') {
            $command = sprintf(
                'start "" /B "%s" "%s" app:send-registration-notifications %d %s',
                str_replace('/', '\\', $phpBinary),
                str_replace('/', '\\', $artisan),
                $userId,
                $type
            );

            pclose(popen('cmd /C "' . $command . ' > NUL 2>&1"', 'r'));

            return;
        }

        $command = sprintf(
            '%s %s app:send-registration-notifications %d %s > /dev/null 2>&1 &',
            escapeshellarg($phpBinary),
            escapeshellarg($artisan),
            $userId,
            escapeshellarg($type)
        );

        pclose(popen($command, 'r'));
    }

    protected function storeImage(mixed $imageFile, bool $useUnderscore = false): ?string
    {
        if (!$imageFile) {
            return null;
        }

        $separator = $useUnderscore ? '_' : '';
        $imageName = time() . $separator . $imageFile->getClientOriginalName();
        $location = public_path('upload');
        $imageFile->move($location, $imageName);

        return url('upload/' . $imageName);
    }

    protected function resolveInitialSellerStatus(int $roleId): string
    {
        return $roleId === 2 ? 'pending_review' : 'approved';
    }
}
