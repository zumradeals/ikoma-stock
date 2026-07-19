<?php

namespace App\Livewire\Sales;

use App\Enums\CustomerType;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Exceptions\Business\BusinessException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\Product;
use App\Modules\Customer\Services\CustomerService;
use App\Modules\Delivery\Services\DeliveryService;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Sale\Services\SaleService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class NewSale extends Component
{
    public int $step = 1;

    public ?int $outletId = null;

    public array $cart = [];

    public ?int $customerId = null;

    public bool $isPassingCustomer = false;

    public ?string $passingPhone = null;

    public string $customerSearch = '';

    public array $paymentLines = [];

    public string $deliveryChoice = 'later';

    public string $discountAmount = '';

    public string $discountPercentage = '';

    public ?Invoice $invoice = null;

    // ── Écran 2 : mode de paiement choisi par le vendeur
    public string $paymentChoice = '';

    // ── Montant reçu maintenant (en francs, saisie libre) pour le mode "later"
    public string $partialAmountInput = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->outletId = $user->outlet_id ?? Outlet::query()->where('company_id', $user->company_id)->value('id');

        if (request('customer_id')) {
            $this->customerId = (int) request('customer_id');
        }
    }

    public function getOutletsProperty()
    {
        return auth()->user()->outlet_id
            ? collect()
            : Outlet::query()->where('company_id', auth()->user()->company_id)->get();
    }

    public function getCustomerProperty(): ?Customer
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    public function getCustomerResultsProperty()
    {
        if ($this->customerSearch === '') {
            return collect();
        }

        return Customer::query()
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->customerSearch}%")
                    ->orWhere('phone', 'like', "%{$this->customerSearch}%");
            })
            ->limit(10)
            ->get();
    }

    #[On('cart.add')]
    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit->value,
                'unit_price' => $product->sale_price,
                'quantity' => 1,
            ];
        }
    }

    #[On('cart.update-quantity')]
    public function updateCartQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            unset($this->cart[$productId]);

            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] = $quantity;
        }
    }

    #[On('cart.remove')]
    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    #[On('cart.choose-customer')]
    public function goToCustomerStep(): void
    {
        $this->step = 3;
    }

    #[On('cart.checkout')]
    public function goToPaymentStep(): void
    {
        $this->step = 2;
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->isPassingCustomer = false;
        $this->customerSearch = '';
    }

    public function usePassingCustomer(): void
    {
        $this->isPassingCustomer = true;
        $this->customerId = null;
    }

    #[On('customer-alert.continue')]
    public function customerAlertContinue(): void
    {
        // Rien à faire : le vendeur reste sur l'étape client, libre de cliquer "Suivant".
    }

    #[On('customer-alert.block')]
    public function customerAlertBlock(): void
    {
        $this->customerId = null;
    }

    public function updatedPaymentChoice(): void
    {
        $this->partialAmountInput = '';
        $this->syncPaymentLines();
    }

    public function updatedDeliveryChoice(): void
    {
        // I4 : si livraison différée et pas de client, préparer le mode "passage"
        if ($this->deliveryChoice === 'later' && ! $this->hasIdentifiedCustomer()) {
            $this->isPassingCustomer = true;
        }
    }

    public function updatedPartialAmountInput(): void
    {
        if ($this->paymentChoice === 'later') {
            $this->syncPaymentLines();
        }
    }

    public function nextStep(): void
    {
        // I3 : si reste > 0 et aucun client identifié, bloquer au passage de l'étape 3
        if ($this->step === 3) {
            if ($this->remainingAmount > 0 && ! $this->hasIdentifiedCustomer()) {
                $this->addError('customer', 'Ce client n\'a pas tout payé. Ajoute son numéro pour suivre ce qu\'il doit.');

                return;
            }
        }

        // I4 : si livraison différée et aucun client identifié, bloquer au passage de l'étape 4
        if ($this->step === 4) {
            if ($this->deliveryChoice === 'later' && ! $this->hasIdentifiedCustomer()) {
                $this->addError('delivery', 'Pour livrer plus tard, ajoute le numéro du client pour pouvoir le retrouver.');

                return;
            }
        }

        // Depuis l'étape 2, sauter l'étape 3 (client) si rien n'est dû
        if ($this->step === 2 && $this->remainingAmount === 0) {
            $this->step = 4;

            return;
        }

        $this->step = min(5, $this->step + 1);
    }

    public function previousStep(): void
    {
        // Depuis l'étape 4, sauter l'étape 3 en arrière si rien n'était dû
        if ($this->step === 4 && $this->remainingAmount === 0) {
            $this->step = 2;

            return;
        }

        $this->step = max(1, $this->step - 1);
    }

    public function addPaymentLine(): void
    {
        $this->paymentLines[] = ['method' => PaymentMethod::CASH->value, 'amount' => 0];
    }

    public function removePaymentLine(int $index): void
    {
        unset($this->paymentLines[$index]);
        $this->paymentLines = array_values($this->paymentLines);
    }

    public function getCartTotalProperty(): int
    {
        return collect($this->cart)->sum(fn (array $line) => $line['unit_price'] * $line['quantity']);
    }

    public function getNetTotalProperty(): int
    {
        return max(0, $this->cartTotal - $this->discountTotal);
    }

    public function getCanApplyDiscountProperty(): bool
    {
        return in_array(auth()->user()->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true);
    }

    /**
     * Reproduit le calcul de SaleService::applyDiscount() pour afficher le
     * même montant avant validation (le pourcentage l'emporte sur le
     * montant fixe si les deux sont renseignés).
     */
    public function getDiscountTotalProperty(): int
    {
        if (! $this->canApplyDiscount) {
            return 0;
        }

        $percentage = (float) ($this->discountPercentage ?: 0);

        if ($percentage > 0) {
            return (int) round($this->cartTotal * $percentage / 100);
        }

        return (int) round(((float) ($this->discountAmount ?: 0)) * 100);
    }

    public function getPaidAmountProperty(): int
    {
        return collect($this->paymentLines)->sum(fn (array $line) => (int) $line['amount'] * 100);
    }

    public function getRemainingAmountProperty(): int
    {
        return max(0, $this->netTotal - $this->paidAmount);
    }

    public function validateSale(): void
    {
        $this->validate([
            'cart' => 'required|array|min:1',
        ], [
            'cart.required' => 'Le panier est vide.',
        ]);

        $user = auth()->user();
        $sales = app(SaleService::class);

        $customerId = null;
        $customerType = CustomerType::PASSING;

        if ($this->customerId) {
            $customerId = $this->customerId;
            $customerType = CustomerType::REGISTERED;
        } elseif ($this->isPassingCustomer && $this->passingPhone) {
            $customer = app(CustomerService::class)->createOrFindPassingCustomer($this->passingPhone);
            $customerId = $customer->id;
        }

        $primaryMethod = collect($this->paymentLines)->sortByDesc('amount')->first();

        $sale = $sales->createDraft([
            'company_id' => $user->company_id,
            'outlet_id' => $this->outletId,
            'user_id' => $user->id,
            'customer_id' => $customerId,
            'customer_type' => $customerType,
            'payment_method_primary' => $primaryMethod ? PaymentMethod::from($primaryMethod['method']) : PaymentMethod::CUSTOMER_CREDIT,
        ]);

        foreach ($this->cart as $line) {
            $sales->addLine($sale, Product::findOrFail($line['product_id']), $line['quantity']);
        }

        try {
            if ($this->canApplyDiscount && $this->discountTotal > 0) {
                $percentage = (int) round((float) ($this->discountPercentage ?: 0));
                $amount = (int) round(((float) ($this->discountAmount ?: 0)) * 100);
                $sales->applyDiscount($sale->fresh(), $amount, $percentage);
            }

            $invoice = $sales->validate($sale->fresh());
        } catch (BusinessException $e) {
            // La vente est restée DRAFT (jamais réservé de stock ni facturé) : on la
            // supprime pour ne pas laisser un brouillon orphelin, comme le ferait
            // SaleService::cancel() sur une vente DRAFT.
            $sale->fresh()->saleLines()->delete();
            $sale->delete();
            $this->addError('form', $e->getMessage());

            return;
        }

        foreach ($this->paymentLines as $line) {
            $amount = (int) $line['amount'] * 100;

            if ($amount > 0) {
                try {
                    app(PaymentService::class)->record($invoice, $amount, PaymentMethod::from($line['method']));
                } catch (BusinessException $e) {
                    // La vente/facture est déjà validée : on ne l'annule pas, on
                    // redirige vers la facture avec un message expliquant que ce
                    // paiement précis n'a pas pu être enregistré (les paiements
                    // précédents de la boucle, eux, sont bien restés enregistrés).
                    session()->flash('status', "Vente validée, mais : {$e->getMessage()}");
                    $this->redirect(route('sales.show', $sale), navigate: true);

                    return;
                }
            }
        }

        if ($this->deliveryChoice === 'now') {
            $invoice->refresh();
            $lines = $invoice->sale->saleLines->mapWithKeys(fn ($l) => [$l->id => $l->quantity])->all();
            app(DeliveryService::class)->deliver($invoice, $lines);
        }

        $this->invoice = $invoice->fresh();
        $this->step = 6;
    }

    public function render()
    {
        return view('livewire.sales.new-sale');
    }

    // ── Privé ──────────────────────────────────────────────────────────────

    public function getHasIdentifiedCustomerProperty(): bool
    {
        return $this->customerId !== null
            || ($this->isPassingCustomer && ! empty($this->passingPhone));
    }

    private function hasIdentifiedCustomer(): bool
    {
        return $this->hasIdentifiedCustomer;
    }

    private function syncPaymentLines(): void
    {
        $netFrancs = $this->netTotal / 100;

        $this->paymentLines = match ($this->paymentChoice) {
            'cash_now'   => [['method' => PaymentMethod::CASH->value, 'amount' => $netFrancs]],
            'mobile_now' => [['method' => PaymentMethod::MOBILE_MONEY->value, 'amount' => $netFrancs]],
            'later'      => (float) $this->partialAmountInput > 0
                ? [['method' => PaymentMethod::CASH->value, 'amount' => (float) $this->partialAmountInput]]
                : [],
            default      => [],
        };
    }
}
