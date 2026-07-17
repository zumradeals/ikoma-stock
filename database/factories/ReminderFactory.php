<?php

namespace Database\Factories;

use App\Enums\ReminderChannel;
use App\Models\Company;
use App\Models\Receivable;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'receivable_id' => Receivable::factory(),
            'user_id' => User::factory(),
            'reminder_date' => now(),
            'channel' => ReminderChannel::WHATSAPP,
            'message_sent' => 'Merci de régulariser votre solde en attente.',
            'customer_response' => null,
            'next_reminder_scheduled_at' => now()->addDays(7),
        ];
    }
}
