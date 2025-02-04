<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;
    protected $mailer;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @param string $mailer
     */
    public function __construct($token, $mailer = 'smtp')
    {
        $this->token = $token;
        $this->mailer = $mailer; // Dynamic mailer selection
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('reset-password/?token=' . $this->token);

        return (new MailMessage)
            ->mailer($this->mailer) // Specify which mailer to use
            ->from('no-reply@example.com', 'Your App') // Change sender dynamically if needed
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, no further action is required.');
    }
}