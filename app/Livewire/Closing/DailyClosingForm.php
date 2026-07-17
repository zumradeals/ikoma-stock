<?php

namespace App\Livewire\Closing;

use App\Enums\DailyClosingStatus;
use App\Enums\InvoiceDeliveryStatus;
use App\Enums\SaleStatus;
use App\Models\DailyClosing;
use App\Models\DeliveryLine;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Sale;
use App\Modules\DailyClosing\Services\DailyClosingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class DailyClosingForm extends Component
{
    public ?DailyClosing $closing = null;

    public bool $noOutlet = false;

    public int $declaredCash = 0;

    public ?string $observations = null;

    public string $rejectReason = '';

    public function mount(): void
    {
        $user = auth()->user();
        $outletId = $user->outlet_id
            ?? ($user->company_id ? Outlet::query()->where('company_id', $user->company_id)->value('id') : null);

        if (! $outletId) {
            $this->noOutlet = true;

            return;
        }

        $outlet = Outlet::findOrFail($outletId);

        $this->closing = app(DailyClosingService::class)->openForToday($outlet, $user);

        if ($this->closing->status !== DailyClosingStatus::VALIDATED) {
            $this->linkTodaysPayments();
        }

        $this->observations = $this->closing->observations;
    }

    protected function linkTodaysPayments(): void
    {
        $service = app(DailyClosingService::class);

        Payment::query()
            ->whereNull('daily_closing_id')
            ->whereDate('payment_date', $this->closing->business_date)
            ->whereHas('invoice.sale', fn ($q) => $q->where('outlet_id', $this->closing->outlet_id))
            ->get()
            ->each(fn (Payment $payment) => $service->addPayment($this->closing, $payment));

        $this->closing->refresh();
    }

    public function getSummaryProperty(): array
    {
        return app(DailyClosingService::class)->computeSummary($this->closing);
    }

    public function getOldReceivablesCollectedProperty(): int
    {
        return (int) $this->closing->payments()
            ->whereHas('invoice.sale', fn ($q) => $q->whereDate('created_at', '<', $this->closing->business_date))
            ->sum('amount');
    }

    public function getDiscountsProperty(): int
    {
        return (int) Sale::query()
            ->where('outlet_id', $this->closing->outlet_id)
            ->whereDate('created_at', $this->closing->business_date)
            ->sum('discount_amount');
    }

    public function getCancelledInvoicesProperty(): int
    {
        return Sale::query()
            ->where('outlet_id', $this->closing->outlet_id)
            ->whereDate('created_at', $this->closing->business_date)
            ->where('status', SaleStatus::CANCELLED->value)
            ->count();
    }

    public function getDeliveredProductsProperty(): int
    {
        return (int) DeliveryLine::query()
            ->whereHas('delivery', fn ($q) => $q
                ->whereDate('delivered_at', $this->closing->business_date)
                ->whereHas('invoice.sale', fn ($qq) => $qq->where('outlet_id', $this->closing->outlet_id))
            )
            ->sum('quantity_delivered');
    }

    public function getUndeliveredOrdersProperty(): int
    {
        return Invoice::query()
            ->whereHas('sale', fn ($q) => $q->where('outlet_id', $this->closing->outlet_id))
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->count();
    }

    public function getDifferenceProperty(): int
    {
        return ($this->declaredCash * 100) - $this->summary['cash'];
    }

    public function getCanValidateProperty(): bool
    {
        return auth()->user()->can('validate', $this->closing);
    }

    public function close(): void
    {
        app(DailyClosingService::class)->submitForValidation(
            $this->closing,
            $this->declaredCash * 100,
            $this->observations,
        );

        $this->closing->refresh();
    }

    public function requestValidate(): void
    {
        $this->dispatch(
            'confirm-action',
            title: 'Valider le point de journée',
            message: 'Confirmer la validation ? Cette action est définitive.',
            detail: null,
            danger: false,
            eventName: 'closing.validate.confirmed',
            eventParams: [],
        );
    }

    #[On('closing.validate.confirmed')]
    public function validateClosing(): void
    {
        app(DailyClosingService::class)->validate($this->closing, auth()->user());
        $this->closing->refresh();
    }

    public function reject(): void
    {
        $this->validate(['rejectReason' => 'required|string|min:3']);

        $this->dispatch(
            'confirm-action',
            title: 'Rejeter le point de journée',
            message: 'Confirmer le rejet ?',
            detail: $this->rejectReason,
            danger: true,
            eventName: 'closing.reject.confirmed',
            eventParams: [],
        );
    }

    #[On('closing.reject.confirmed')]
    public function rejectClosing(): void
    {
        app(DailyClosingService::class)->reject($this->closing, auth()->user(), $this->rejectReason);
        $this->closing->refresh();
        $this->rejectReason = '';
    }

    public function render()
    {
        return view('livewire.closing.daily-closing-form');
    }
}
