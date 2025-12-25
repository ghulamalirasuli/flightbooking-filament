<?php
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Ensure this is present

trait UsesBranchTimezone
{
    public static function bootUsesBranchTimezone()
    {
        static::creating(function ($model) {
            $model->setBranchTimezone(true);
        });

        static::updating(function ($model) {
            $model->setBranchTimezone(false);
        });
    }

    protected function setBranchTimezone($isCreating)
    {
        $branchTimezone = null;
        $user = null;

        if (Auth::guard('web')->user()) {
            $user = Auth::guard('web')->user();
            // Log::info('UsesBranchTimezone: Web Guard User ID: ' . $user->id . ' | Branch ID: ' . ($user->branch_id ?? 'NULL'));
            if ($user->branch) {
                // Log::info('UsesBranchTimezone: Web Guard User Branch Loaded. Branch ID: ' . $user->branch->id . ' | Branch Timezone from object: ' . ($user->branch->timezone ?? 'NULL'));
                $branchTimezone = $user->branch->timezone;
            } else {
                // Log::warning('UsesBranchTimezone: Web Guard User Branch relationship is NULL or not loaded.');
            }
        } elseif (Auth::guard('account')->user()) {
            $user = Auth::guard('account')->user();
            // Log::info('UsesBranchTimezone: Account Guard User ID: ' . $user->id . ' | Branch Name ID: ' . ($user->branchName->id ?? 'NULL'));
            if ($user->branchName) {
                // Log::info('UsesBranchTimezone: Account Guard User BranchName Loaded. BranchName ID: ' . $user->branchName->id . ' | Branch Timezone from object: ' . ($user->branchName->timezone ?? 'NULL'));
                $branchTimezone = $user->branchName->timezone;
            } else {
                // Log::warning('UsesBranchTimezone: Account Guard User BranchName relationship is NULL or not loaded.');
            }
        } else {
            // Log::warning('UsesBranchTimezone: No user authenticated via web or account guards.');
        }
    
        // Log::info('UsesBranchTimezone: Final Resolved Branch Timezone: ' . ($branchTimezone ?? 'null (defaulting to app timezone)'));
        
        $nowInBranchTimezone = Carbon::now($branchTimezone);
        
        // Log::info('UsesBranchTimezone: Carbon Time in Branch Timezone: ' . $nowInBranchTimezone->toDateTimeString() . ' (' . $nowInBranchTimezone->timezoneName . ')');

        // ... (rest of your existing code for setting date_comment, date_confirm, date_update, created_at, updated_at)
        if (isset($this->attributes['date_comment'])) {
            $this->date_comment = $nowInBranchTimezone->format('Y-m-d');
        }
        // if (isset($this->attributes['date_confirm'])) {
        //     $this->date_confirm = $nowInBranchTimezone->format('Y-m-d');
        // }
        // if (isset($this->attributes['date_update'])) {
        //     $this->date_update = $nowInBranchTimezone->format('Y-m-d');
        // }
// Only set date_confirm to 'now' if it wasn't already set by the form
if (isset($this->attributes['date_confirm']) && empty($this->date_confirm)) {
    $this->date_confirm = $nowInBranchTimezone->format('Y-m-d');
}

// Only set date_update to 'now' if it wasn't already set by the form
if (isset($this->attributes['date_update']) && empty($this->date_update)) {
    $this->date_update = $nowInBranchTimezone->format('Y-m-d');
}
        if (isset($this->attributes['inserted'])) {
            $this->inserted = $nowInBranchTimezone->format('Y-m-d H:i:s');
        }
        if (isset($this->attributes['updated'])) {
            $this->updated = $nowInBranchTimezone->format('Y-m-d H:i:s');
        }

        if ($isCreating) {
            $this->created_at = $nowInBranchTimezone;
            $this->updated_at = $nowInBranchTimezone;
        } else {
            $this->updated_at = $nowInBranchTimezone;
        }
    }
}