<?php
/*
namespace App\Notifications;

use App\Models\Comments;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Comments $comment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Comment Reminder',
            'subject' => $this->comment->subject,
            'comment_id' => $this->comment->id,
            'reference_no' => $this->comment->reference_no,
            'date' => now()->toDateTimeString(),
        ];
    }
}
    */


namespace App\Notifications;

use App\Models\Comments;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Comments $comment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        // Filament-compatible format
        return [
            'title' => 'Comment Reminder: ' . $this->comment->subject,
            'body' => $this->comment->comments,
            'icon' => 'heroicon-o-chat-bubble-left',
            'status' => 'warning',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'View Comment',
                    'url' => '/admin/transactions/' . $this->comment->reference_no,
                    'markAsRead' => true,
                ]
            ],
            // Additional data
            'comment_id' => $this->comment->id,
            'reference_no' => $this->comment->reference_no,
        ];
    }
}