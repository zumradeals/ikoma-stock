<?php

namespace App\Modules\Dashboard\Services;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReceivableStatus;
use App\Enums\SaleStatus;
use App\Enums\TransferStatus;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\Transfer;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Agrégations en lecture seule pour le tableau de bord. Cache 5 min, clés
 * TOUJOURS préfixées par company_id — sans ça, une clé de cache globale
 * partagée entre tenants romprait l'isolation multi-tenant au niveau du
 * cache, même si les requêtes SQL sous-jacentes sont bien scopées.
 */
class DashboardService
{
    protected const TTL = 300;

    public function todaySales(Company $company): array
    {
        return $this->remember($company, 'today-sales', function () use ($company) {
            $sales = Sale::query()
                ->where('company_id', $company->id)
                ->whereDate('created_at', now()->toDateString())
                ->where('status', SaleStatus::VALIDATED->value)
                ->get();

            return [
                'by_outlet' => $sales->groupBy('outlet_id')->map(fn ($group) => $group->sum('total_amount'))->all(),
                'by_seller' => $sales->groupBy('user_id')->map(fn ($group) => $group->sum('total_amount'))->all(),
                'total' => (int) $sales->sum('total_amount'),
            ];
        });
    }

    public function cashCollected(Company $company): int
    {
        return $this->remember($company, 'cash-collected', fn () => (int) Payment::query()
            ->where('company_id', $company->id)
            ->whereDate('payment_date', now()->toDateString())
            ->where('method', PaymentMethod::CASH->value)
            ->sum('amount'));
    }

    public function outstandingReceivables(Company $company): int
    {
        return $this->remember($company, 'outstanding-receivables', fn () => (int) Receivable::query()
            ->where('company_id', $company->id)
            ->where('status', '!=', ReceivableStatus::PAID->value)
            ->sum('balance_due'));
    }

    public function unpaidDeliveries(Company $company): Collection
    {
        return $this->remember($company, 'unpaid-deliveries', fn () => Invoice::query()
            ->where('company_id', $company->id)
            ->whereNotIn('delivery_status', [InvoiceDeliveryStatus::DELIVERED->value, InvoiceDeliveryStatus::CANCELLED->value])
            ->get());
    }

    /**
     * quantity_physical est stocké en centièmes, low_stock_threshold en
     * unités réelles — on ramène le seuil sur la même échelle avant
     * comparaison.
     */
    public function lowStockAlerts(Company $company): Collection
    {
        return $this->remember($company, 'low-stock-alerts', fn () => Product::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->with('stockLevels')
            ->get()
            ->filter(fn (Product $product) => $product->stockLevels->sum('quantity_physical') <= $product->low_stock_threshold * 100)
            ->values());
    }

    /**
     * Somme(qty réelle × prix d'achat), en centimes. quantity_physical
     * (centièmes) × cost_price (centimes) / 100 pour revenir à l'échelle
     * centimes attendue en sortie.
     */
    public function stockValue(Company $company): int
    {
        return $this->remember($company, 'stock-value', fn () => (int) Product::query()
            ->where('company_id', $company->id)
            ->with('stockLevels')
            ->get()
            ->sum(fn (Product $product) => intdiv($product->stockLevels->sum('quantity_physical') * ($product->cost_price ?? 0), 100)));
    }

    public function transfersInTransit(Company $company): Collection
    {
        return $this->remember($company, 'transfers-in-transit', fn () => Transfer::query()
            ->where('company_id', $company->id)
            ->whereIn('status', [TransferStatus::SHIPPED->value, TransferStatus::PARTIALLY_RECEIVED->value])
            ->get());
    }

    public function topSellers(Company $company, string $period = 'month'): array
    {
        return $this->remember($company, "top-sellers-{$period}", function () use ($company, $period) {
            $since = match ($period) {
                'day' => now()->startOfDay(),
                'week' => now()->startOfWeek(),
                'year' => now()->startOfYear(),
                default => now()->startOfMonth(),
            };

            return Sale::query()
                ->where('company_id', $company->id)
                ->where('status', SaleStatus::VALIDATED->value)
                ->where('created_at', '>=', $since)
                ->groupBy('user_id')
                ->selectRaw('user_id, SUM(total_amount) as total, COUNT(*) as sales_count')
                ->orderByDesc('total')
                ->get()
                ->toArray();
        });
    }

    /**
     * Équivalent de Cache::remember(), mais traite une valeur en cache
     * corrompue (__PHP_Incomplete_Class — ex. suite à un worker PHP-FPM tué
     * en plein milieu d'une écriture) comme une absence de cache plutôt que
     * de la renvoyer telle quelle (ce qui ferait planter tout appelant qui
     * type-hint un retour Collection/array).
     */
    protected function remember(Company $company, string $key, Closure $callback): mixed
    {
        $cacheKey = "dashboard:{$company->id}:{$key}";
        $cached = Cache::get($cacheKey);

        if ($cached !== null && ! $cached instanceof \__PHP_Incomplete_Class) {
            return $cached;
        }

        $fresh = $callback();
        Cache::put($cacheKey, $fresh, static::TTL);

        return $fresh;
    }
}
