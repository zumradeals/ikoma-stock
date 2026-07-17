<?php

namespace Database\Seeders;

use App\Enums\CustomerType;
use App\Enums\DailyClosingStatus;
use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Enums\LocationType;
use App\Enums\PaymentMethod;
use App\Enums\ReceivableStatus;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Enums\TransferStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DailyClosing;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Models\Transfer;
use App\Models\TransferLine;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DocumentNumberGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Génère une entreprise de démo complète : équipe, points de vente, dépôt,
 * catalogue produits, clients, et un historique minimal (mouvements de
 * stock, ventes, factures, paiements, créance, transfert, point de journée).
 */
class CompanyDemoSeeder extends Seeder
{
    public function run(array $config): void
    {
        $generator = app(DocumentNumberGenerator::class);
        $slug = Str::slug($config['name']);

        $company = Company::create([
            'name' => $config['name'],
            'address' => 'Zone 4, Abidjan',
            'phone' => '+225 27 22 00 00 00',
            'email' => "contact@{$slug}.ci",
            'currency' => 'XOF',
            'invoice_prefix' => $config['invoice_prefix'],
            'logo_path' => 'companies/placeholder.svg',
            'footer_text' => "Merci de votre confiance — {$config['name']}",
            'is_active' => true,
        ]);

        $admin = User::factory()->for($company)->role(UserRole::ADMIN_COMPANY)->create([
            'name' => "Admin {$config['name']}",
            'email' => "admin@{$slug}.ci",
        ]);

        $sellers = User::factory()->count(2)->for($company)->role(UserRole::SELLER)->create();

        $warehouseKeeper = User::factory()->for($company)->role(UserRole::WAREHOUSE_KEEPER)->create();

        $warehouse = Warehouse::factory()->for($company)->create(['manager_id' => $admin->id]);

        $outlets = Outlet::factory()->count(2)->for($company)->create(['manager_id' => $admin->id]);

        $sellers->each(function (User $seller, int $i) use ($outlets) {
            $seller->update(['outlet_id' => $outlets[$i % $outlets->count()]->id]);
        });

        $categories = collect($config['categories'])->map(
            fn (string $name) => Category::factory()->for($company)->create([
                'name' => $name,
                'image_path' => 'categories/placeholder.svg',
            ])
        );

        $products = collect(range(1, 10))->map(function () use ($company, $categories) {
            return Product::factory()->for($company)->for($categories->random())->create([
                'image_path' => 'products/placeholder.svg',
            ]);
        });

        $products->each(function (Product $product) use ($company, $warehouse, $warehouseKeeper) {
            $quantity = fake()->numberBetween(50, 500) * 100;

            StockLevel::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'location_type' => LocationType::WAREHOUSE,
                'location_id' => $warehouse->id,
                'quantity_physical' => $quantity,
                'quantity_reserved' => 0,
            ]);

            StockMovement::create([
                'company_id' => $company->id,
                'product_id' => $product->id,
                'movement_type' => StockMovementType::INITIAL_ENTRY,
                'quantity' => $quantity,
                'location_destination_type' => LocationType::WAREHOUSE,
                'location_destination_id' => $warehouse->id,
                'reason' => 'Stock initial',
                'user_id' => $warehouseKeeper->id,
                'movement_date' => now(),
            ]);
        });

        $customers = Customer::factory()->count(5)->for($company)->create();

        // Historique minimal : 5 ventes, dont 2 à crédit (facture non soldée).
        for ($i = 1; $i <= 5; $i++) {
            $seller = $sellers->random();
            $outlet = $outlets->random();
            $product = $products->random();
            $quantity = fake()->numberBetween(1, 5);
            $unitPrice = $product->sale_price;
            $lineTotal = $quantity * $unitPrice;
            $isCredit = $i > 3;
            $customer = $isCredit ? $customers->random() : null;

            $sale = Sale::create([
                'company_id' => $company->id,
                'number' => $generator->generate('sales', $company->id, 'VTE'),
                'outlet_id' => $outlet->id,
                'user_id' => $seller->id,
                'customer_id' => $customer?->id,
                'customer_type' => $customer ? CustomerType::REGISTERED : CustomerType::PASSING,
                'total_amount' => $lineTotal,
                'discount_amount' => 0,
                'discount_percentage' => 0,
                'payment_method_primary' => $isCredit ? PaymentMethod::CUSTOMER_CREDIT : PaymentMethod::CASH,
                'status' => SaleStatus::VALIDATED,
            ]);

            SaleLine::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_discount' => 0,
                'line_total' => $lineTotal,
            ]);

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'sale_id' => $sale->id,
                'number' => $generator->generate('invoices', $company->id, $company->invoice_prefix),
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'total_amount' => $lineTotal,
                'paid_amount' => $isCredit ? 0 : $lineTotal,
                'balance_due' => $isCredit ? $lineTotal : 0,
                'payment_status' => $isCredit ? InvoicePaymentStatus::UNPAID : InvoicePaymentStatus::PAID,
                'delivery_status' => InvoiceDeliveryStatus::DELIVERED,
            ]);

            if ($isCredit) {
                Receivable::create([
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'initial_amount' => $lineTotal,
                    'total_paid' => 0,
                    'balance_due' => $lineTotal,
                    'due_date' => now()->addDays(30),
                    'status' => ReceivableStatus::OPEN,
                ]);
            } else {
                Payment::create([
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $lineTotal,
                    'method' => PaymentMethod::CASH,
                    'payment_date' => now(),
                    'user_id' => $seller->id,
                ]);
            }
        }

        // 1 transfert dépôt -> point de vente
        $transferProduct = $products->random();
        $transferQuantity = 2000;

        $transfer = Transfer::create([
            'company_id' => $company->id,
            'number' => $generator->generate('transfers', $company->id, 'TRF'),
            'source_warehouse_id' => $warehouse->id,
            'destination_outlet_id' => $outlets->first()->id,
            'user_id' => $warehouseKeeper->id,
            'status' => TransferStatus::RECEIVED,
            'total_quantity' => $transferQuantity,
            'shipped_quantity' => $transferQuantity,
            'received_quantity' => $transferQuantity,
            'request_date' => now(),
            'ship_date' => now(),
            'receive_date' => now(),
        ]);

        TransferLine::create([
            'transfer_id' => $transfer->id,
            'product_id' => $transferProduct->id,
            'requested_quantity' => $transferQuantity,
            'shipped_quantity' => $transferQuantity,
            'received_quantity' => $transferQuantity,
        ]);

        // 1 point de journée validé pour le premier point de vente
        DailyClosing::create([
            'company_id' => $company->id,
            'outlet_id' => $outlets->first()->id,
            'user_id' => $sellers->first()->id,
            'business_date' => now(),
            'total_sales' => 150_000 * 100,
            'cash_sales' => 100_000 * 100,
            'mobile_money_sales' => 50_000 * 100,
            'transfer_sales' => 0,
            'credit_sales' => 0,
            'collected_old_receivables' => 0,
            'total_discounts' => 0,
            'cancelled_invoices_count' => 0,
            'delivered_products_count' => 5,
            'declared_cash_amount' => 100_000 * 100,
            'cash_difference' => 0,
            'status' => DailyClosingStatus::VALIDATED,
            'validated_by_user_id' => $admin->id,
            'validated_at' => now(),
        ]);

        Subscription::factory()->for($company)->create();
    }
}
