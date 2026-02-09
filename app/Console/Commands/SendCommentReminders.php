<?php
/*
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Comments;
use Filament\Notifications\Notification;
use Filament\Actions\Action; // Correct namespace for v4

class SendCommentReminders extends Command
{
    protected $signature = 'comments:send-reminders';
    protected $description = 'Send comment reminder notifications';

    public function handle(): int
    {
        $comments = Comments::query()
            ->where('reminder', 'yes')
            ->where('reminder_notified', false)
            ->whereNotNull('date_comment')
            ->where('date_comment', '<=', now())
            ->get();

        $this->info('Found ' . $comments->count() . ' comments');

        foreach ($comments as $comment) {
            if ($comment->user) {
                Notification::make()
                    ->title('Comment Reminder: ' . $comment->subject)
                    ->body($comment->comments)
                    ->icon('heroicon-o-chat-bubble-left')
                    ->warning()
                    ->actions([
                        Action::make('view') // Now using correct namespace
                            ->label('View Comment')
                            ->url('/admin/transactions/' . $comment->reference_no)
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($comment->user);

                $this->info("Notified user ID {$comment->user->id}");
            }

            $comment->update(['reminder_notified' => true]);
        }

        return self::SUCCESS;
    }
}
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Comments;
use App\Models\User; // Add this
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class SendCommentReminders extends Command
{
    protected $signature = 'comments:send-reminders';
    protected $description = 'Send comment reminder notifications';

    public function handle(): int
    {
        $comments = Comments::query()
            ->where('reminder', 'yes')
            ->where('reminder_notified', false)
            ->whereNotNull('date_comment')
            ->where('date_comment', '<=', now())
            ->get();

        $this->info('Found ' . $comments->count() . ' comments');

        // Get all users who should receive notifications
        $allUsers = User::all(); // Or filter by role/permission if needed

        foreach ($comments as $comment) {
            // Send to ALL users, not just comment creator
            foreach ($allUsers as $user) {
                Notification::make()
                    ->title('Comment Reminder: ' . $comment->subject)
                    ->body($comment->comments)
                    ->icon('heroicon-o-chat-bubble-left')
                    ->warning()
                    ->actions([
                        Action::make('view')
                            ->label('View Comment')
                            ->url('/admin/transactions/' . $comment->reference_no)
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($user);
            }

            $this->info("Notified all users for comment ID {$comment->id}");
            $comment->update(['reminder_notified' => true]);
        }

        return self::SUCCESS;
    }
}