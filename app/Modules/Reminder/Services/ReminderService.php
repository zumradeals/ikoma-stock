<?php

namespace App\Modules\Reminder\Services;

use App\Enums\ReminderChannel;
use App\Models\Receivable;
use App\Models\Reminder;
use DateTimeInterface;

class ReminderService
{
    public function record(Receivable $receivable, ReminderChannel $channel, string $message): Reminder
    {
        $reminder = Reminder::create([
            'company_id' => $receivable->company_id,
            'receivable_id' => $receivable->id,
            'user_id' => auth()->id(),
            'reminder_date' => now(),
            'channel' => $channel,
            'message_sent' => $message,
        ]);

        $receivable->update(['last_reminder_at' => now()]);

        return $reminder;
    }

    public function generateWhatsappMessage(Receivable $receivable): string
    {
        $customer = $receivable->customer;
        $company = $receivable->company;
        $amount = number_format(intdiv($receivable->balance_due, 100), 0, ',', ' ');

        return "Bonjour {$customer->name}, nous vous rappelons que votre solde de {$amount} FCFA auprès de {$company->name} reste à régler. Merci de votre compréhension.";
    }

    public function scheduleNext(Receivable $receivable, DateTimeInterface $date): void
    {
        $receivable->update(['next_reminder_at' => $date]);
    }
}
