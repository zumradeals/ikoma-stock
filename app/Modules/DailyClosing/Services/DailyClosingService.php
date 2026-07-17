<?php

namespace App\Modules\DailyClosing\Services;

use App\Enums\DailyClosingStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\Business\DailyClosingLockedException;
use App\Models\DailyClosing;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\User;
use LogicException;

class DailyClosingService
{
    public function openForToday(Outlet $outlet, User $user): DailyClosing
    {
        return DailyClosing::firstOrCreate(
            ['outlet_id' => $outlet->id, 'business_date' => now()->toDateString()],
            [
                'company_id' => $outlet->company_id,
                'user_id' => $user->id,
                'status' => DailyClosingStatus::OPEN,
            ],
        );
    }

    public function addPayment(DailyClosing $dailyClosing, Payment $payment): void
    {
        $this->guardUnlocked($dailyClosing);

        $payment->update(['daily_closing_id' => $dailyClosing->id]);
    }

    public function computeSummary(DailyClosing $dailyClosing): array
    {
        $payments = $dailyClosing->payments;

        return [
            'cash' => (int) $payments->where('method', PaymentMethod::CASH)->sum('amount'),
            'mobile_money' => (int) $payments->where('method', PaymentMethod::MOBILE_MONEY)->sum('amount'),
            'bank_transfer' => (int) $payments->where('method', PaymentMethod::BANK_TRANSFER)->sum('amount'),
            'check' => (int) $payments->where('method', PaymentMethod::CHECK)->sum('amount'),
            'customer_credit' => (int) $payments->where('method', PaymentMethod::CUSTOMER_CREDIT)->sum('amount'),
            'other' => (int) $payments->where('method', PaymentMethod::OTHER)->sum('amount'),
            'total' => (int) $payments->sum('amount'),
        ];
    }

    public function submitForValidation(DailyClosing $dailyClosing, int $declaredCash, ?string $observations): void
    {
        $this->guardTransition($dailyClosing, DailyClosingStatus::PENDING_VALIDATION);

        $summary = $this->computeSummary($dailyClosing);

        $dailyClosing->update([
            'status' => DailyClosingStatus::PENDING_VALIDATION,
            'declared_cash_amount' => $declaredCash,
            'cash_difference' => $declaredCash - $summary['cash'],
            'observations' => $observations,
            'cash_sales' => $summary['cash'],
            'mobile_money_sales' => $summary['mobile_money'],
        ]);
    }

    /**
     * Verrouille définitivement le point de journée : après VALIDATED,
     * aucune modification n'est possible (voir guardUnlocked() et
     * DailyClosingPolicy).
     */
    public function validate(DailyClosing $dailyClosing, User $user): void
    {
        $this->guardTransition($dailyClosing, DailyClosingStatus::VALIDATED);

        $dailyClosing->update([
            'status' => DailyClosingStatus::VALIDATED,
            'validated_by_user_id' => $user->id,
            'validated_at' => now(),
        ]);
    }

    public function reject(DailyClosing $dailyClosing, User $user, string $reason): void
    {
        $this->guardTransition($dailyClosing, DailyClosingStatus::REJECTED);

        $dailyClosing->update([
            'status' => DailyClosingStatus::REJECTED,
            'observations' => trim(($dailyClosing->observations ? $dailyClosing->observations.' | ' : '')."Rejeté par {$user->name} : {$reason}"),
        ]);
    }

    protected function guardUnlocked(DailyClosing $dailyClosing): void
    {
        if ($dailyClosing->status === DailyClosingStatus::VALIDATED) {
            throw new DailyClosingLockedException;
        }
    }

    protected function guardTransition(DailyClosing $dailyClosing, DailyClosingStatus $target): void
    {
        if ($dailyClosing->status === DailyClosingStatus::VALIDATED) {
            throw new DailyClosingLockedException;
        }

        if (! $dailyClosing->status->canTransitionTo($target)) {
            throw new LogicException("Transition de point de journée {$dailyClosing->status->value} → {$target->value} non autorisée.");
        }
    }
}
