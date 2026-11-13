<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends text messages through Twilio (https://www.twilio.com).
 * Enabled only when TWILIO_SID, TWILIO_TOKEN and TWILIO_FROM are set.
 */
class SmsChannel
{
    public static function isConfigured(): bool
    {
        return filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'))
            && filled(config('services.twilio.from'));
    }

    /**
     * Phone number in international format (+15551234567). US numbers without a country code get +1.
     * Returns null when the number can't be used.
     */
    public static function normalize(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with(trim($phone), '+')) {
            return strlen($digits) >= 8 ? '+' . $digits : null;
        }
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        return null;
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $to = self::normalize($notifiable->routeNotificationFor('sms', $notification));
        if (!$to || !self::isConfigured()) {
            return;
        }

        $sid = config('services.twilio.sid');

        $response = Http::asForm()
            ->withBasicAuth($sid, config('services.twilio.token'))
            ->timeout(10)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => config('services.twilio.from'),
                'Body' => $notification->toSms($notifiable),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Twilio SMS failed (' . $response->status() . '): ' . $response->json('message', $response->body()));
        }
    }
}
