# IKOMA STOCK — Règles métier

Ce document liste chaque règle métier du cahier des charges, la
classe/méthode qui l'implémente, l'exception levée si elle est violée, et le
test Pest qui la couvre. Il complète `SCHEMA.md` (modèle de données) avec la
couche services/state machines/policies construite par-dessus.

## Machines à états

Implémentées en **enum + méthodes** (`App\Enums\Concerns\HasTransitions`),
pas `spatie/laravel-model-states` — ce package n'a pas de version stable
compatible Laravel 13 (voir écart documenté dans le schéma). Chaque enum
expose `canTransitionTo(self $target): bool`.

### Sale.status (`App\Enums\SaleStatus`)

| De | Vers | Déclencheur |
|---|---|---|
| DRAFT | VALIDATED | `SaleService::validate()` |
| VALIDATED | CANCELLED | `SaleService::cancel()` |

DRAFT n'a pas de transition vers CANCELLED : un brouillon n'a jamais réservé
de stock ni généré de facture, `SaleService::cancel()` le supprime
directement au lieu de le faire transiter par un état annulé. Transition
invalide → `SaleValidationForbiddenException`.

### Invoice.delivery_status (`App\Enums\InvoiceDeliveryStatus`)

Indépendant de `payment_status` (colonnes distinctes, brief §Invoice). Graphe
dans `InvoiceDeliveryStatus::transitions()` : `TO_PREPARE` → READY/
PARTIAL_DELIVERED/DELIVERED/CANCELLED ; `READY` → PARTIAL_DELIVERED/
DELIVERED/CANCELLED ; `PARTIAL_DELIVERED` → lui-même (livraisons partielles
successives)/DELIVERED/CANCELLED. `markReady()` est optionnel : une
livraison peut être enregistrée directement depuis TO_PREPARE.

### Invoice.payment_status (`App\Enums\InvoicePaymentStatus`)

**Pas une machine à états à transitions manuelles** — le brief la décrit
comme *dérivée* de `paid_amount`/`total_amount`/`due_date`
(`InvoicePaymentStatus::computeFor()`, appelée par
`PaymentService::applyToInvoice()` à chaque paiement/remboursement) :

1. `CANCELLED` gagne toujours (état manuel, ne se recalcule jamais tout seul).
2. `paid_amount >= total_amount` → `PAID`.
3. Sinon, échéance dépassée → `OVERDUE`.
4. Sinon, `paid_amount > 0` → `PARTIAL`.
5. Sinon → `UNPAID`.

### Transfer.status (`App\Enums\TransferStatus`)

`DRAFT` → REQUESTED/CANCELLED ; `REQUESTED` → ACCEPTED/CANCELLED ;
`ACCEPTED` → IN_PREPARATION/**SHIPPED**/CANCELLED ; `IN_PREPARATION` →
SHIPPED ; `SHIPPED` → RECEIVED/PARTIALLY_RECEIVED ; `PARTIALLY_RECEIVED` →
RECEIVED. Annulation seulement avant expédition (le stock n'a pas encore
bougé). **Écart assumé** : ACCEPTED → SHIPPED est autorisé directement car
`TransferService` n'expose pas de méthode dédiée pour marquer
IN_PREPARATION séparément — `ship()` peut donc sauter cet état
intermédiaire optionnel. Transition invalide non couverte par une exception
nommée du brief → `LogicException` générique.

### DailyClosing.status (`App\Enums\DailyClosingStatus`)

`OPEN` → PENDING_VALIDATION ; `PENDING_VALIDATION` → VALIDATED/REJECTED ;
`REJECTED` → PENDING_VALIDATION (resoumission après correction).
`VALIDATED` est terminal et verrouillé : toute tentative de modification
après validation lève `DailyClosingLockedException` (service ET policy,
voir plus bas).

## Services et règles couvertes

| Service | Méthode | Règle | Exception si violée | Test |
|---|---|---|---|---|
| `StockService` | `reserveForSale` | Ne réserve jamais plus que le disponible (physique − réservé), verrouillé via `lockForUpdate()` | `InsufficientStockException` | `StockServiceTest` |
| | `confirmDelivery` | Décrémente physique + réservé, trace un `StockMovement` SALE_DELIVERY | — | `StockServiceTest` |
| | `processTransfer` | Déplace exactement le delta de CET appel (pas le cumul) — nécessaire pour les réceptions partielles successives | `InsufficientStockException` (ship) | `StockServiceTest`, `TransferServiceTest` |
| | `createInventoryCorrection` | Ajustement libre (+/-), toujours tracé | — | `StockServiceTest` |
| `SaleService` | `addLine`/`applyDiscount` | Interdit hors DRAFT | `SaleValidationForbiddenException` | `SaleServiceTest` |
| | `applyDiscount` | Remise réservée à ADMIN_COMPANY/OUTLET_MANAGER (`SalePolicy`) | `UnauthorizedPriceModificationException` | `SaleServiceTest` |
| | `validate` | Réserve le stock, génère la facture, crée la créance si solde + client identifié — transaction unique | `InsufficientStockException` (rollback complet, aucune facture créée) | `SaleServiceTest` |
| | `cancel` | DRAFT : suppression directe. VALIDATED : libère le stock + annule la facture, permission requise | `SaleValidationForbiddenException` | `SaleServiceTest` |
| `InvoiceService` | `markOverdue` | Ne touche jamais une facture PAID/CANCELLED | — | `InvoiceServiceTest` |
| | `cancel` | Si payée : crée un avoir (remboursement). Si non livrée : libère la réservation | — | `InvoiceServiceTest` |
| | (modèle `Invoice`) | Une facture n'est **jamais** supprimable, à n'importe quel point d'appel | `InvoiceDeletionForbiddenException` (levée par `Invoice::booted()->deleting`) | `InvoicePolicy` testé dans `PoliciesTest` |
| `PaymentService` | `record` | `amount > 0` et `amount <= balance_due` sauf overpayment explicite | `InvalidArgumentException` (montant ≤ 0) / `PaymentExceedsBalanceException` | `PaymentServiceTest` |
| | `record`/`refund` | MAJ atomique de `Invoice.paid_amount/payment_status` + `Receivable` liée | — | `PaymentServiceTest`, `ReceivableServiceTest` |
| `DeliveryService` | `deliver` | Quantité livrée cumulée ≤ quantité commandée (`SaleLine.delivered_quantity`) | `DeliveryExceedsOrderedQuantityException` | `DeliveryServiceTest` |
| | `deliver` | Bascule DELIVERED si toutes les lignes sont soldées, sinon PARTIAL_DELIVERED | — | `DeliveryServiceTest` |
| `TransferService` | `ship`/`receive` | Respecte le graphe `TransferStatus` | `LogicException` | `TransferServiceTest` |
| | `receive` | Réceptions partielles cumulables jusqu'à réception complète | — | `TransferServiceTest` |
| `CustomerService` | `createOrFindPassingCustomer` | Dédoublonnage par téléphone au sein d'une société | — | `CustomerServiceTest` |
| `ReceivableService` | `syncFromInvoice` | Pas de créance sans client identifié (FK NOT NULL) | (retourne `null`, pas d'exception) | `ReceivableServiceTest` |
| `DailyClosingService` | toute méthode de mutation | Aucune modification après VALIDATED | `DailyClosingLockedException` | `DailyClosingServiceTest` |
| | `validate`/`reject` (policy) | Le déclarant ne peut pas valider son propre point de journée (maker-checker) | Gate refusé (`DailyClosingPolicy::validate`) | `PoliciesTest` |
| `DashboardService` | toutes | Cache 5 min, clé **toujours** préfixée par `company_id` | — (test dédié à la non-fuite inter-tenant) | `DashboardServiceTest` |

## Exceptions métier (`app/Exceptions/Business/`)

Toutes étendent `BusinessException` (code HTTP + `render()` JSON
automatique via la convention Laravel — pas de câblage supplémentaire dans
le handler global).

| Exception | HTTP | Message |
|---|---|---|
| `InsufficientStockException` | 422 | Stock disponible insuffisant pour "{produit}" : X disponible(s) pour Y demandé(s). |
| `PaymentExceedsBalanceException` | 422 | Le paiement de {montant} dépasse le solde restant dû ({solde}). |
| `DeliveryExceedsOrderedQuantityException` | 422 | La quantité à livrer (X) dépasse la quantité restant à livrer (Y). |
| `InvoiceDeletionForbiddenException` | 403 | La suppression d'une facture n'est jamais autorisée ; utilisez l'annulation. |
| `SaleValidationForbiddenException` | 403 | Cette opération est interdite sur cette vente : {raison}. |
| `CrossTenantAccessException` | 403 | Accès refusé : cette ressource appartient à une autre entreprise. |
| `DailyClosingLockedException` | 423 | Ce point de journée est validé et verrouillé ; aucune modification n'est possible. |
| `UnauthorizedPriceModificationException` | 403 | Vous n'êtes pas autorisé à modifier le prix de ce produit. |

`CrossTenantAccessException` existe pour un usage futur direct dans un
contrôleur/policy HTTP (ex: un contrôleur qui veut un message plus
spécifique que le 403 générique d'`EnsureTenantAccess`) ; l'isolation
tenant elle-même reste garantie par `CompanyScope` +
`EnsureTenantAccess` (voir tâche précédente), qui ne l'utilisent pas
directement.

Pour les cas de garde qui ne correspondent à aucune des 8 exceptions
nommées (ex : montant de paiement ≤ 0, transition d'état non couverte sur
`Transfer`/`Invoice.delivery_status`/`DailyClosing`), le code lève une
exception standard PHP (`InvalidArgumentException`, `LogicException`) —
toujours typée, jamais un `return false`, conformément à la règle générale
du brief, simplement sans réinventer un nom "métier" pour une erreur de
programmation/état générique.

## Policies (`app/Policies/`)

Auto-découvertes par convention Laravel (`App\Models\X` →
`App\Policies\XPolicy`), pas d'enregistrement manuel nécessaire. Toutes
utilisent `GrantsSuperAdmin` (trait `before()`) sauf `InvoicePolicy`, qui
définit son propre `before()` pour que **'delete' reste refusé même pour un
SUPER_ADMIN** (aucun rôle ne peut supprimer une facture).

| Policy | Ability | Règle |
|---|---|---|
| `SalePolicy` | `applyDiscount`, `cancel` | ADMIN_COMPANY, OUTLET_MANAGER uniquement |
| `InvoicePolicy` | `cancel` | ADMIN_COMPANY, OUTLET_MANAGER |
| | `delete` | Toujours refusé, y compris SUPER_ADMIN |
| `TransferPolicy` | `manage` | ADMIN_COMPANY, OUTLET_MANAGER, WAREHOUSE_KEEPER |
| `DailyClosingPolicy` | `update` | Refusé si VALIDATED, sinon tout rôle interne |
| | `validate`, `reject` | ADMIN_COMPANY/OUTLET_MANAGER, **jamais l'auteur du point de journée** (maker-checker) |
| `ProductPolicy` | `updatePrice` | ADMIN_COMPANY, OUTLET_MANAGER uniquement |

## Interprétations et écarts documentés

- **Entité `Delivery`/`DeliveryLine`** : absente du schéma initial (tâche
  précédente) — ajoutée ici (2 migrations additives) car
  `DeliveryService::deliver(): Delivery` et
  `DeliveryExceedsOrderedQuantityException` supposent un événement de
  livraison traçable, distinct des `StockMovement`. `sale_lines
  .delivered_quantity` (colonne additive) évite de resommer
  `delivery_lines` à chaque contrôle.
- **`processTransfer(Transfer, direction, quantities)`** : signature
  étendue au-delà du brief (`Transfer, 'ship'|'receive'`) avec un 3ᵉ
  paramètre `$quantities` — indispensable pour que les réceptions
  partielles successives ne déplacent que le delta de l'appel en cours, pas
  le cumul stocké sur la ligne.
- **"Avoir" sur facture payée annulée** (`InvoiceService::cancel`) : pas de
  nouvelle entité CreditNote — `PaymentService::refund()` crée un paiement
  négatif (méthode OTHER, note explicite). `paid_amount`/`balance_due` sont
  bornés à `[0, total_amount]` (les CHECK de la tâche précédente l'exigent) ;
  un éventuel surplus de remboursement au-delà du payé est plafonné, pas
  stocké en négatif.
- **Surpaiement autorisé** (`PaymentService::record(..., allowOverpayment:
  true)`) : même contrainte — `Invoice.paid_amount` ne dépasse jamais
  `total_amount` (CHECK `paid_amount <= total_amount`), le `Payment` de
  ledger garde lui le montant réellement encaissé. Le surplus devient un
  avoir client non modélisé plus loin dans ce périmètre.
- **`CustomerService::createOrFindPassingCustomer`/
  `transformPassingToRegistered`** : le schéma n'a pas de colonne "type" sur
  `customers` (`CustomerType` REGISTERED/PASSING vit sur `Sale`, pas ici).
  Une fiche `Customer` minimale (juste le téléphone, nom "Client passager")
  est donc considérée "passagère" tant qu'elle n'a pas été complétée par
  `transformPassingToRegistered()`.
- **Concurrence** : Pest ne permet pas de test multi-processus réel. Le test
  "deux ventes simultanées" (`StockServiceTest`) vérifie la règle métier
  réelle — deux réservations séquentielles sur un stock tout juste
  suffisant pour une seule, la seconde étant rejetée — ce qui démontre que
  `lockForUpdate()` empêche la survente, sans prétendre reproduire du vrai
  parallélisme multi-thread.
- **`AuditService`** : point de passage unique pour `ActivityLog`, appelé
  automatiquement par `HasAudit` (refactoré pour déléguer à ce service) et
  disponible pour tout appel direct depuis un service sur une action
  sensible qui ne correspond pas à un événement Eloquent simple.

## Vérification

- `php artisan migrate:fresh --seed` — les 4 migrations additives
  s'appliquent proprement, le seed de la tâche précédente reste valide.
- `vendor/bin/pest` — 94 tests, 194 assertions, tous verts.
- `vendor/bin/pest --coverage --coverage-filter=app/Modules --min=80` — les
  12 services couvrent entre 94,9 % et 100 % chacun (moyenne ≈ 98 %),
  largement au-dessus du seuil demandé ; couverture totale du projet 87,8 %.
