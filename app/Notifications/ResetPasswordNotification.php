<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Get the password reset notification mail message.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Reset your password')
            ->markdown('emails.auth.reset-password', [
                'url' => $url,
                'expire' => $expire,
                'appName' => config('app.name'),
            ]);
    }

    protected function resetUrl($notifiable): string
    {
        $email = urlencode($notifiable->getEmailForPasswordReset());
        $token = $this->token;

        return rtrim(config('app.frontend_url'), '/') . "/reset-password?token={$token}&email={$email}";
    }
}
