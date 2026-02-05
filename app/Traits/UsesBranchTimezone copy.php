<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait UsesBranchTimezone
{
    protected static function bootUsesBranchTimezone(): void
    {
        /**
         * IMPORTANT:
         * Use `saving`, not `creating` or `updating`
         * Filament finalizes form data BEFORE `saving`
         */
        static::saving(function ($model) {
            $model->applyBranchTimezone();
        });
    }

    protected function applyBranchTimezone(): void
    {
        $branchTimezone = config('app.timezone');

        /**
         * Resolve branch timezone from authenticated user
         */
        if ($user = Auth::guard('web')->user()) {
            $branchTimezone = $user->branch?->timezone ?? $branchTimezone;
        } elseif ($user = Auth::guard('account')->user()) {
            $branchTimezone = $user->branchName?->timezone ?? $branchTimezone;
        }

        $now = Carbon::now($branchTimezone);

        /**
         * ONLY auto-set business dates if user did NOT provide them
         */
        $this->setIfNull('date_comment', $now->format('Y-m-d H:i:s'));
        $this->setIfNull('date_confirm', $now->format('Y-m-d'));
        $this->setIfNull('date_update', $now->format('Y-m-d'));

        /**
         * Handle timestamps safely
         * Let Laravel manage timestamps, only adjust timezone
         */
        if (! $this->exists && empty($this->created_at)) {
            $this->created_at = $now;
        }

        $this->updated_at = $now;
    }

    /**
     * Helper: only set field if it exists AND is null
     */
    protected function setIfNull(string $field, string $value): void
    {
        if (
            array_key_exists($field, $this->attributes)
            && is_null($this->attributes[$field])
        ) {
            $this->attributes[$field] = $value;
        }
    }
}
