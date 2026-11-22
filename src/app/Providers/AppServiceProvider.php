<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ActivityPolicy;
use Filament\Actions\MountableAction;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureLocalMailRecipient();
        $this->protectStaffFromDeletion();

        Gate::policy(
            Activity::class,
            ActivityPolicy::class
        );

        Page::formActionsAlignment(
            Alignment::Right
        );

        Notifications::alignment(
            Alignment::End
        );

        Notifications::verticalAlignment(
            VerticalAlignment::End
        );

        Page::$reportValidationErrorUsing = function (
            ValidationException $exception
        ): void {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };

        MountableAction::configureUsing(
            function (MountableAction $action): void {
                $action->modalFooterActionsAlignment(
                    Alignment::Right
                );
            }
        );
    }

    /**
     * Pertahanan tambahan bila pengguna memiliki permission langsung
     * atau lebih dari satu role: Staff tetap tidak boleh menghapus data.
     */
    private function protectStaffFromDeletion(): void
    {
        Gate::before(static function (User $user, string $ability, array $arguments): ?bool {
            if (! $user->hasRole('petugas') || $user->hasRole('super_admin')) {
                return null;
            }

            // Permission langsung tidak boleh membuka akses administrasi.
            if (
                preg_match('/^(view(_any)?|create|update|delete(_any)?)_(role|activity|token)$/', $ability)
                || preg_match('/^(create|update|delete(_any)?)_user$/', $ability)
            ) {
                return false;
            }

            $target = $arguments[0] ?? null;
            $modelTarget = $target instanceof Model
                || (is_string($target) && is_subclass_of($target, Model::class));

            if (
                $modelTarget
                && in_array($ability, [
                    'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny',
                    'restore', 'restoreAny', 'replicate', 'reorder',
                ], true)
            ) {
                return false;
            }

            if (preg_match('/^(delete(_any)?|force_delete(_any)?|restore(_any)?|replicate|reorder)_/', $ability)) {
                return false;
            }

            return null;
        });
    }

    private function configureLocalMailRecipient(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        if (
            ! (bool) config(
                'mail.testing_recipient.enabled',
                false
            )
        ) {
            return;
        }

        $address = trim(
            (string) config(
                'mail.testing_recipient.address',
                ''
            )
        );

        if ($address === '') {
            return;
        }

        /*
         * Jangan memasukkan display name pada penerima.
         */
        Mail::alwaysTo($address);
    }
}