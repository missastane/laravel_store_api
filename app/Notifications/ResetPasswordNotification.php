<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    protected $token;
    private $details;
    /**
     * Create a new notification instance.
     */
    public function __construct($token, $details)
    {
        $this->token = $token;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('بازیابی رمز عبور')
            ->line('درخواست تغییر رمز عبور دریافت شد')
            ->action('تغییر رمز عبور', url("/api/password/reset?token={$this->token}&email={$notifiable->email}"))
            ->line('اگر این درخواست توسط شما انجام نشده است، این پیام را نادیده بگیرید');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {

        date_default_timezone_set('Asia/Tehran');
        return [
            'message' => $this->details['message'],
            'datetime' => date('Y-m-d H:i:s'),
        ];
    }
}
