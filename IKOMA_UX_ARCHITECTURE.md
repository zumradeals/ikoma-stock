# IKOMA STOCK — Document de référence UX
### Repenser l'expérience sans toucher au moteur métier
Version 1.0 — préparé pour être exécuté par Claude Code, sprint par sprint

---

## 0. Pourquoi ce document existe

Le moteur métier d'Ikoma Stock est solide : machines à états explicites, policies par rôle,
exceptions métier nommées, `payment_status` dérivé automatiquement, remises déjà
réservées à `ADMIN_COMPANY`/`OUTLET_MANAGER`. Ce n'est pas un projet bricolé — c'est un
backend Laravel/Livewire bien structuré.

Le problème n'est pas le moteur. Le problème est que **l'interface parle le langage d'un
logiciel de gestion, alors que l'utilisateur final est un vendeur de quincaillerie, de fer,
de ciment, souvent peu à l'aise avec l'écrit et jamais formé sur un outil numérique.**

Ce document ne remplace pas le moteur. Il pose les règles pour reconstruire les parcours
par-dessus lui, sans rien casser.

**Deux points de vérité technique confirmés en lisant le code** (pas des suppositions) :

1. **Bug réel, pas juste un problème d'affichage** : `InvoicePaymentStatus::computeFor()`
   n'est appelé que depuis `PaymentService::record()` (`app/Modules/Payment/Services/PaymentService.php:86`).
   Dans `NewSale.php::validateSale()`, une ligne de paiement à `0 F` ne déclenche jamais
   `PaymentService::record()` (garde `if ($amount > 0)`). Donc une vente à remise totale
   (total = 0) reste **UNPAID pour toujours** — jamais recalculée en PAID. C'est visible sur
   ta capture "FAC-202607-0002 — Non payée". Correction ciblée en §7.1.
2. **La remise n'est pas un trou d'architecture** : `SalePolicy::applyDiscount` la réserve déjà
   à `ADMIN_COMPANY`/`OUTLET_MANAGER` (propriété `canApplyDiscount` dans `NewSale.php`). Le
   compte utilisé sur les captures a probablement ce rôle. Le vrai manque : **l'UI ne
   distingue jamais visuellement "je suis un vendeur" de "je suis un responsable qui peut
   déroger au prix"**, et aucune confirmation renforcée n'existe avant une remise importante.
   C'est réparable en pur UX.

Conclusion : on garde 100 % du moteur (`SaleService`, `PaymentService`, `StockService`,
`DeliveryService`, les enums, les policies). On reconstruit les parcours, les écrans, le
vocabulaire, et on ajoute quelques garde-fous UI + deux corrections ciblées côté service.

---

## 1. Les personas réels

| Persona | Qui | Ce qu'il fait 50 fois par jour | Rapport à l'écrit/numérique |
|---|---|---|---|
| **Le vendeur** (rôle `SALESPERSON` / employé outlet) | Personne au comptoir, souvent le patron lui-même dans les petits commerces | Vend, encaisse, note qui doit encore payer, dit ce qui est livré ou non | Lit des chiffres et des noms familiers avec aisance ; lit mal les phrases longues ou les mots techniques/anglais ; peu de patience pour un écran qui ne "répond pas à sa question" |
| **Le responsable** (`ADMIN_COMPANY` / `OUTLET_MANAGER`) | Patron, gérant de boutique | Vérifie les ventes du jour, autorise les remises, clôture la caisse, gère le stock et les produits | Plus à l'aise avec les chiffres et un peu plus avec l'écrit, mais veut de la vitesse, pas un tableau de bord de contrôleur de gestion |
| **Le magasinier** (`WAREHOUSE_KEEPER`) | Gère les entrées/sorties physiques, les transferts entre dépôts | Réceptionne, transfère, corrige le stock | Rapport très concret au produit (le voit, le touche), peu d'intérêt pour les écrans financiers |

**Règle de conception n°1** : un même écran ne doit jamais mélanger la tâche du vendeur et la
tâche du responsable. Aujourd'hui, l'écran Stock mélange "consulter" (vendeur) et
"administrer le catalogue" (responsable). C'est la cause principale de la confusion relevée
dans tes captures.

---

## 2. Les quatre gestes métier — l'ossature de toute l'app

Tout ce que fait un vendeur se ramène à quatre gestes. L'app doit être organisée autour
d'eux, pas autour des tables de la base de données.

1. **Je vends** → `NewSale`
2. **Je reçois de l'argent** → `PaymentForm`
3. **Je livre** → `DeliveryService`
4. **Je vérifie ce qui reste** → stock en lecture seule

Tout le reste (catégories, export PDF, transferts, clôtures, création produit) est **secondaire**
et doit vivre dans un espace clairement séparé, réservé au responsable/magasinier.

---

## 3. Invariants métier à protéger dans l'UI (contrats non négociables)

Ces règles existent déjà en grande partie côté moteur ; l'UI doit les rendre **impossibles à
contourner par accident**, pas seulement les documenter.

| # | Invariant | État actuel dans le moteur | Ce que l'UI doit garantir |
|---|---|---|---|
| I1 | Une facture à `total_amount = 0` doit être `PAID`, jamais `UNPAID` | Bug confirmé (§0.1) | Corriger le service (§7.1) + afficher "🎁 Offert" et non "Non payée" |
| I2 | Une remise qui annule 100 % du prix est un acte exceptionnel | Autorisée sans confirmation renforcée si le rôle le permet | Écran de confirmation dédié avec motif obligatoire avant validation (§6.3) |
| I3 | Un client de passage avec un reste à payer > 0 ne doit jamais sortir de marchandise sans trace | Le service crée bien une créance uniquement si `customer_id` identifié (`ReceivableService::syncFromInvoice`) — donc un client de passage non identifié avec un reste **ne génère aucune créance suivie**, comme le dit le message affiché | Bloquer "Suivant" si `reste > 0` ET client = passage sans téléphone ; forcer la saisie nom + téléphone |
| I4 | Une livraison différée doit toujours être rattachée à un client retrouvable | Le moteur ne l'impose pas au niveau UI actuel | Si "Livrer plus tard", exiger un client identifié (nom + téléphone minimum) |
| I5 | Le statut affiché doit toujours refléter l'état réel (payé/livré/dette), pas juste "Validée" | `SaleStatus` (DRAFT/VALIDATED/CANCELLED) est un statut technique, différent du statut métier perçu | Calculer un badge composite à l'affichage (§5.3), ne jamais montrer "Validée" seule à l'utilisateur final |
| I6 | Une remise ne doit être visible/activable que pour les rôles autorisés | Déjà fait (`SalePolicy`) | Ne même pas afficher le champ remise à un vendeur simple — pas juste le désactiver |

---

## 4. Architecture de navigation cible

### Espace Vendeur (rôle par défaut)
Barre basse à **4 icônes maximum**, verbes d'action, pas des noms de table :

```
🏠 Accueil   🛒 Vendre   💰 Paiements   👥 Clients
```

- *Stock* n'est plus un onglet séparé pour le vendeur : la disponibilité est visible
  directement dans le catalogue de vente (badge sur chaque produit). Un vendeur n'a pas
  besoin d'un écran dédié pour "regarder" le stock, il en a besoin au moment de vendre.
- *Livraisons* n'est pas un onglet séparé non plus : elle vit dans la fiche de chaque vente
  ("📦 à livrer" → un tap → confirmer livraison), et une liste "à livrer aujourd'hui" est
  une carte sur l'accueil, pas un menu.
- Pas de "Plus" avec trois petits points. Si une fonction est assez importante pour être
  utilisée, elle a une place nommée. Si elle est secondaire, elle va dans l'espace
  responsable.

### Espace Responsable (accessible via un bouton "Gestion" clairement séparé, pas mélangé)
```
📊 Tableau de bord   📦 Stock & produits   🔁 Transferts   🧾 Clôtures   ⚙️ Réglages
```
C'est ici, et seulement ici, que vivent : nouvelle catégorie, nouveau produit, export PDF,
suppression, transferts, clôture de caisse, gestion des remises exceptionnelles.

**Règle de conception n°2** : le changement d'espace doit être un choix explicite et visible
("Je passe en mode Gestion"), jamais un mélange silencieux sur le même écran.

---

## 5. Refonte écran par écran

### 5.1 Connexion — P0

**Aujourd'hui** : email + mot de passe, mélange français/anglais, message d'erreur
administratif ("Ces identifiants ne correspondent pas à nos enregistrements.").

**Cible** :
- Un seul champ visible par défaut : **numéro de téléphone**.
- Puis code **PIN à 4 chiffres**, saisie type digicode (gros pavé numérique tactile).
- Tout en français : "Numéro de téléphone", "Code", bouton **"Entrer"**.
- Erreur : *"Code incorrect. Réessaie ou appelle le responsable."*
- "Se souvenir de moi" activé par défaut sur l'appareil (un vendeur ne doit pas se
  reconnecter 10 fois par jour).
- Compatibilité technique : le champ `email` peut rester la clé d'authentification côté
  Laravel (`auth.php` inchangé) — l'UI mappe simplement `phone` → recherche de l'email
  associé côté serveur avant `Auth::attempt()`. Aucun changement de schéma requis dans
  l'immédiat ; une colonne `phone` unique + `pin_hash` peut être ajoutée en migration
  additive plus tard sans toucher au reste.

### 5.2 Accueil vendeur — P0

**Aujourd'hui** : tableau de bord de contrôleur de gestion (8 indicateurs + 2 listes).

**Cible** — trois zones, dans cet ordre :

1. **Trois gros boutons d'action**, pleine largeur, icône + verbe :
   `🛒 Vendre` · `💰 Encaisser un paiement` · `📦 Livrer un client`
2. **Ce qui est urgent aujourd'hui**, en phrases, pas en jargon :
   - "3 clients doivent encore payer — 45 000 F"
   - "2 commandes à livrer aujourd'hui"
   - "1 produit presque épuisé"
   (chaque ligne est tapable → ouvre directement l'action correspondante)
3. Le reste (ventes par point de vente, par vendeur, transferts, clôtures) part dans
   l'espace Responsable → Tableau de bord.

### 5.3 Écran des ventes / historique

**Aujourd'hui** : `VTE-202607-0001`, `50 000 XOF`, badge `Validée`.

**Cible** : un badge de **statut métier composite**, jamais le statut technique brut.
Logique d'affichage (calculée, pas stockée — dérivée de `payment_status` +
`delivery_status`) :

| payment_status | delivery_status | Badge affiché |
|---|---|---|
| PAID | DELIVERED | ✅ Payé et livré |
| PAID | TO_PREPARE / READY / PARTIAL_DELIVERED | 📦 Payé — à livrer |
| PARTIAL | * | 💰 Reste à payer |
| UNPAID + total=0 (après correction §7.1 : devient PAID) | — | 🎁 Offert |
| UNPAID (total>0) | * | ⏳ Non payé |
| CANCELLED | * | ❌ Annulée |

La ligne d'historique devient :
```
Aujourd'hui à 16h08 · Client de passage · 50 000 F     ✅ Payé et livré
```
La référence technique (`VTE-202607-0001`) reste disponible dans le détail, en petit,
pour le support et l'export — jamais comme information principale.

### 5.4 Catalogue de vente

**Bon déjà en place** : grande image, prix visible, bouton `+`. À corriger :

- Grille de tuiles plus compactes (2 colonnes sur mobile) pour ne pas perdre de vitesse
  avec un catalogue de plusieurs dizaines de produits — actuellement une seule carte
  occupe toute la largeur.
- Vraies photos produit obligatoires à la création (le placeholder "GamaDrive" affiché sur
  "Barre de Fer" est trompeur — c'est un logo d'app, pas un produit). Champ image
  **obligatoire** côté formulaire "Nouveau produit" (espace Responsable), avec une image
  générique par catégorie en secours (fer à béton générique, sac de ciment générique, etc.)
  plutôt qu'un logo d'application.
- Unité affichée toujours entière et contractuelle : `250 barres disponibles`, jamais
  `249.98 Barre`. Le moteur peut garder une précision décimale interne (pertinent pour du
  fer au poids/mètre) ; l'affichage arrondit toujours à l'unité de vente déclarée sur le
  produit (`unit` — déjà un enum dans le modèle `Product`, donc le contrat existe déjà côté
  base : `barre`, `sac`, `tonne`, `kg`, `mètre`, `pièce`... il suffit de ne jamais le
  contourner par du texte libre côté formulaire produit).
- Bouton d'ajout : icône **+ mot** ("Ajouter"), pas l'icône seule.

### 5.5 Le parcours de vente — refonte complète du flux en 5 étapes

**Aujourd'hui** : Panier → Client → Paiement (avec remise mélangée dedans) → Livraison →
Récapitulatif. Cinq barres de progression abstraites, vocabulaire de facturier
(Total/Payé/Reste/Récapitulatif).

**Cible** — une question par écran, formulée comme un humain la poserait :

**Écran 1 — "Que vend-on ?"**
Catalogue (inchangé dans son principe, corrigé selon §5.4). Le panier reste visible en bas,
persistant, avec le total en gros.

**Écran 2 — "Le client paie comment ?"**
Quatre grandes options illustrées, exclusives :
```
💵 Tout maintenant, en espèces
📱 Tout maintenant, Mobile Money
🤝 Il paiera plus tard (une partie ou tout)
```
*(l'option "gratuit/remise totale" n'apparaît **pas** ici pour un vendeur simple — elle
n'existe que dans l'espace Responsable, cf. I6 et §5.6)*

Si "une partie" ou "plus tard" est choisi → champ unique **"Montant reçu maintenant"**, le
reste est calculé et affiché en direct : *"Il restera à payer : 10 000 F"*.

**Écran 3 — "Qui est le client ?"** *(uniquement si un reste > 0, sinon écran sauté
automatiquement — cf. Écran 2)*
- Recherche par nom/téléphone (existe déjà, `getCustomerResultsProperty`)
- Ou "Nouveau client" → nom + téléphone (2 champs, rien de plus)
- Le bouton "Suivant" reste **désactivé** tant qu'un reste > 0 et qu'aucun client n'est
  identifié (implémente I3). Message si tentative : *"Ce client n'a pas tout payé. Ajoute
  son nom et son numéro pour suivre ce qu'il doit."*

**Écran 4 — "Il repart avec la marchandise aujourd'hui ?"**
```
✅ Oui, il l'emporte maintenant
📦 Non, à livrer plus tard
```
Si "plus tard" → même contrainte que l'écran 3 : client déjà identifié à ce stade (car
sinon comment le retrouver — implémente I4). Si le client n'est pas encore identifié
(cas "tout payé maintenant" + livraison différée), demander nom + téléphone ici.

**Écran 5 — Confirmation, en une phrase, pas un tableau comptable**
```
Le client achète : 1 barre de fer
Il paie : 25 000 F maintenant
Il reste à payer : 0 F
Il emporte la marchandise aujourd'hui

           [ CONFIRMER LA VENTE ]
```
Le bouton porte le sens réel de l'action, jamais "Suivant" :
`Confirmer la vente` / `Enregistrer la dette` / `Confirmer la vente gratuite` (ce dernier
uniquement visible en espace Responsable, cf. I2).

### 5.6 Remise — retirée du parcours vendeur, déplacée et protégée

- Un vendeur simple **ne voit jamais** de champ remise (implémente I6 — actuellement le
  champ est juste caché conditionnellement dans le blade ; il faut vérifier qu'il n'est pas
  simplement `disabled` mais bien absent du DOM pour ce rôle).
- Pour un rôle autorisé (`ADMIN_COMPANY`/`OUTLET_MANAGER`), la remise se fait **avant**
  l'étape paiement, sur l'écran panier, sous la forme "Prix négocié" — jamais mélangée avec
  "montant reçu".
- Si la remise atteint ou dépasse un seuil (proposition : 50 % du prix, configurable dans
  `config/ikoma.php`), écran de confirmation obligatoire :
```
⚠️ Ce produit va sortir presque gratuitement.
Prix normal : 25 000 F
Remise : 25 000 F
Le client paiera : 0 F

Motif (obligatoire) :
○ Cadeau commercial   ○ Produit endommagé
○ Erreur de prix       ○ Autre

     [ Confirmer la remise ]   [ Annuler ]
```
- Le motif est stocké (`SaleLine`/`Sale` a déjà probablement un champ notes, sinon migration
  additive `discount_reason` sur `sales` — non bloquant, à valider en §7).

### 5.7 Stock — P0, le défaut visuel le plus grave après la connexion

**Aujourd'hui** : tableau horizontal qui déborde, colonnes coupées, mélange consultation +
administration (Export PDF, Nouvelle catégorie, Nouveau produit visibles à tous).

**Cible, espace vendeur (lecture seule, si affiché du tout — voir §4, idéalement le stock
n'a même pas d'écran dédié pour le vendeur, il est visible dans le catalogue)** :
Liste de cartes verticales, une par produit, jamais de tableau :
```
Barre de fer
📍 Angré : —          📍 Ikoma POS : 250
Total disponible : 250
```

**Cible, espace Responsable** : le tableau peut rester en version "bureau/tablette large",
mais sur mobile il devient aussi une liste de cartes, avec les actions d'administration
(Export PDF, Nouvelle catégorie, Nouveau produit, modification) déplacées dans un menu
d'actions clairement identifié comme "Gestion du catalogue", jamais au même niveau visuel
que la simple consultation.

---

## 6. Charte d'interface unique

| Élément | Aujourd'hui (incohérent) | Règle cible |
|---|---|---|
| Couleur principale | Orange / bleu / violet selon l'écran | Une seule couleur d'action principale (orange Ikoma), un seul bleu réservé aux liens/infos secondaires, jamais de violet |
| Langue | Français/anglais mélangés (Email, Password, Remember me, LOG IN) | 100 % français dans toute l'app cliente. L'anglais uniquement dans le code, jamais à l'écran |
| Monnaie | `25 000 XOF` | `25 000 F` à l'écran partout ; `XOF` reste en base et sur les documents export/compta officiels |
| Nom de l'app | "Ikoma pos", "Ikoma Group", "IKOMA STOCK" | Un seul nom d'affichage, une seule capitalisation, choisi une fois pour toutes |
| Statuts | Termes techniques (Validée, Non payée alors que total=0) | Toujours le badge composite métier (§5.3) |
| Boutons d'action | "Suivant" générique partout | Le libellé dit ce qui va se passer ("Confirmer la vente", "Enregistrer le paiement") |
| Erreurs | Phrases administratives | Phrase courte, cause + action ("Code incorrect. Réessaie ou appelle le responsable.") |

---

## 7. Ce qui touche le moteur (minime, ciblé, non-négociable pour la cohérence des données)

Le principe reste : **on ne refait pas le moteur**. Mais deux corrections sont trop
importantes pour rester uniquement côté UI, car elles concernent l'intégrité des données,
pas seulement leur présentation.

### 7.1 Corriger le statut d'une facture à total nul (implémente I1)

Dans `SaleService::validate()` (ou juste après, dans `NewSale::validateSale()` avant
`$this->step = 6`), si `$invoice->total_amount === 0`, appeler explicitement le recalcul de
statut (le même mécanisme que `PaymentService` utilise) plutôt que de dépendre d'un paiement
qui n'aura jamais lieu :
```php
if ($invoice->total_amount === 0) {
    $invoice->payment_status = InvoicePaymentStatus::computeFor(
        paidAmount: 0,
        totalAmount: 0,
        dueDate: $invoice->due_date,
        current: $invoice->payment_status,
    );
    $invoice->save();
}
```
C'est un correctif de 5 lignes, testable unitairement, aucun impact sur le reste du moteur.

### 7.2 Empêcher la sortie de stock non tracée pour un client de passage avec dette (implémente I3)

Vérifier dans `SaleService::validate()` (ou en garde côté `NewSale::validateSale()`, ce qui
est suffisant si c'est le seul point d'entrée) qu'on ne peut pas valider une vente avec
`customer_type = PASSING` **et** `reste > 0` sans qu'un numéro de téléphone ait été fourni
(`passingPhone`). Actuellement la garde `if ($this->isPassingCustomer && $this->passingPhone)`
existe déjà pour créer le client — il manque juste le blocage explicite en amont
(aujourd'hui rien n'empêche de valider avec `isPassingCustomer = true` et `passingPhone =
null` et un reste positif). C'est une validation Livewire à ajouter (`$this->addError(...)`),
pas une modification du moteur.

Tout le reste de ce document (§5, §6) est **strictement de l'UI/Blade/Livewire view layer**
et ne touche à aucun service, aucune migration, aucune policy.

---

## 8. Priorités d'exécution

**P0 — avant tout déploiement terrain**
1. Correction 7.1 (statut PAID sur total nul)
2. Correction 7.2 (blocage client de passage + dette non identifiée)
3. Écran de connexion refait (§5.1)
4. Écran Stock : suppression du tableau horizontal sur mobile (§5.7)
5. Parcours de vente en 5 écrans-question (§5.5)
6. Badge de statut composite partout où une vente/facture est affichée (§5.3)
7. Séparation stricte espace Vendeur / espace Responsable (§4)

**P1**
8. Charte visuelle unique (§6)
9. Remise déplacée + écran de confirmation renforcée (§5.6)
10. Vraies photos produits + unités contractuelles (§5.4)
11. Accueil vendeur recentré sur 3 actions (§5.2)

**P2**
12. Assistance vocale / lecture des montants
13. Mode faible connexion (le PWA existe déjà — `public/sw.js`, `manifest.json` — à vérifier/renforcer)
14. Accès rapide par produit favori/récent (le champ `favoritesOnly` existe déjà dans `ProductCatalog.php`, probablement sous-exploité dans l'UI actuelle)

---

## 9. Prompts prêts à l'emploi pour Claude Code

À utiliser un par un, dans l'ordre P0 ci-dessus. Chaque prompt rappelle explicitement de ne
pas toucher au moteur, pour éviter qu'un agent "corrige" au passage une règle métier qui est
en réalité volontaire.

> **Prompt 1 — correctifs moteur ciblés**
> "Dans ce projet Laravel/Livewire, applique exactement les deux correctifs décrits en §7.1
> et §7.2 de IKOMA_UX_ARCHITECTURE.md. Ne modifie aucun autre comportement de
> `SaleService`, `PaymentService`, `InvoicePaymentStatus` ou des policies. Ajoute un test
> Pest pour chacun des deux cas, dans le style des tests existants sous `tests/Feature` ou
> `tests/Unit` correspondant."

> **Prompt 2 — écran de connexion**
> "Refais entièrement `resources/views/livewire/pages/auth/login.blade.php` et le composant
> Livewire associé selon la section 5.1 de IKOMA_UX_ARCHITECTURE.md : téléphone + PIN à 4
> chiffres, tout en français, gros pavé numérique tactile, message d'erreur simple. Le
> mapping téléphone→email d'authentification se fait côté serveur ; `auth.php` et le
> mécanisme `Auth::attempt()` restent inchangés. Si une colonne `phone` n'existe pas encore
> sur `users`, propose la migration additive avant de l'appliquer, ne la crée pas sans
> validation."

> **Prompt 3 — parcours de vente en 5 écrans**
> "Restructure `app/Livewire/Sales/NewSale.php` et sa vue pour suivre exactement le parcours
> décrit en section 5.5 de IKOMA_UX_ARCHITECTURE.md (5 écrans-question au lieu du flux
> actuel panier/client/paiement/livraison/récap). Conserve tel quel tout appel à
> `SaleService`, `PaymentService`, `DeliveryService`, `CustomerService` — seule la
> présentation, le regroupement des étapes et le vocabulaire changent. Implémente les
> invariants I3 et I4 (blocages décrits en §3 et §5.5-écran 3/4) comme des validations
> Livewire côté composant, pas comme des contraintes de base de données."

> **Prompt 4 — badge de statut composite**
> "Crée un helper (ex: `App\Support\SaleStatusPresenter` ou équivalent) qui calcule le badge
> décrit dans le tableau de la section 5.3 à partir de `payment_status` et
> `delivery_status` d'une facture, sans stocker de nouvelle colonne. Remplace l'affichage du
> statut brut par ce badge partout où une vente/facture est listée ou détaillée
> (`sale-detail.blade.php`, l'historique des ventes, et tout autre écran listant des
> factures)."

> **Prompt 5 — écran stock mobile**
> "Remplace le tableau horizontal de l'écran stock par une liste de cartes verticales sur
> mobile, comme décrit en section 5.7. Sur desktop/tablette large, le tableau peut rester.
> Déplace les actions d'administration (Export PDF, Nouvelle catégorie, Nouveau produit,
> édition, suppression) derrière une action clairement intitulée 'Gestion du catalogue',
> visible uniquement pour les rôles `ADMIN_COMPANY`/`OUTLET_MANAGER`/`WAREHOUSE_KEEPER`
> selon les policies déjà existantes."

> **Prompt 6 — séparation espace Vendeur / Responsable**
> "Restructure la navigation basse selon la section 4 : 4 icônes pour l'espace Vendeur
> (Accueil, Vendre, Paiements, Clients), avec un point d'entrée explicite et séparé vers
> l'espace Responsable (Tableau de bord, Stock & produits, Transferts, Clôtures, Réglages)
> pour les rôles autorisés. Utilise les policies/rôles déjà en place, ne crée pas de nouveau
> système de permissions."

---

## 10bis. Transmettre la maquette visuelle (`ikoma-maquette.html`) à Claude Code

La maquette HTML est une référence **visuelle**, pas du code à copier tel quel : elle contient
de faux produits, aucune logique, et une police (Manrope) différente de celle déjà installée
dans le projet (Figtree, `tailwind.config.js`). Sans consigne explicite, un agent peut soit la
recopier littéralement (mauvais : logique métier perdue), soit s'en inspirer vaguement (mauvais :
résultat pas fidèle). La méthode qui marche : **jetons de design d'abord, composants ensuite,
branchement au moteur en dernier — jamais les trois en même temps.**

### Où déposer le fichier
Place `ikoma-maquette.html` à la racine du repo, ou dans un dossier `design/` versionné
(`design/ikoma-maquette.html`). Claude Code (terminal, VS Code ou desktop) le lit comme
n'importe quel fichier local — pas besoin de l'ouvrir dans un navigateur pour lui.

### Étape 1 — extraire les jetons de design (à faire seule, avant tout écran)
> "Lis `design/ikoma-maquette.html`. Extrais uniquement les jetons de design (les variables
> CSS dans `:root`, la police Manrope, les rayons de bordure, les ombres) et intègre-les dans
> `tailwind.config.js` sous `theme.extend.colors` et `theme.extend.fontFamily`, avec des noms
> sémantiques (`orange`, `success`, `gold`, `info`, `danger`, `charcoal`...), pas des noms
> techniques (`--E8590C`). N'importe aucune police manuellement : ajoute Manrope via le
> `@import` déjà utilisé pour Figtree dans le layout principal, et remplace Figtree par
> Manrope partout où la police est appliquée. Ne touche à aucun fichier Livewire ou service à
> cette étape — seulement la configuration Tailwind et le layout."

### Étape 2 — composants Blade réutilisables (toujours seule)
> "À partir des mêmes jetons, crée des composants Blade réutilisables dans
> `resources/views/components/` : un bouton d'action principale (`x-ikoma.button-primary`),
> un bouton secondaire, une carte d'option de vente (`x-ikoma.option-card`), un badge de
> statut (`x-ikoma.status-badge` avec les variantes payé/à livrer/reste à payer/offert/annulée
> décrites dans IKOMA_UX_ARCHITECTURE.md section 5.3), et le composant de navigation basse
> mobile à 4 icônes. Base-toi visuellement sur `design/ikoma-maquette.html` pour les
> espacements, rayons et tailles de police exacts. N'écris aucune logique métier dans ces
> composants : uniquement de la présentation, ils reçoivent leurs données en props."

### Étape 3 — seulement maintenant, reconstruire les écrans réels
Utilise les prompts de la section 9 (Prompt 2 à 6) un par un, en ajoutant à chacun cette
phrase : *"Utilise les composants `x-ikoma.*` créés précédemment et respecte fidèlement
`design/ikoma-maquette.html` pour la disposition. Les données affichées dans la maquette sont
fictives — utilise les vraies données via les propriétés Livewire existantes."*

### Étape 4 — vérification de fidélité
Après chaque écran refait, demande une comparaison explicite :
> "Prends une capture d'écran de [nom de la page] sur mobile (375px) et compare-la visuellement
> à `design/ikoma-maquette.html` section correspondante. Liste les écarts de couleur,
> d'espacement ou de police avant de continuer."
Si Claude Code dans ton environnement a accès à un navigateur/outil de capture, il peut le
faire lui-même ; sinon, fais-le toi-même et renvoie la capture dans le chat pour comparaison.

### Piège à éviter explicitement
Ne donne jamais un seul prompt du type "refais toute l'app comme la maquette" — sur un projet
de cette taille (`app/Livewire/Sales` à lui seul fait plus de 1000 lignes), un agent qui tente
tout en un seul passage va soit casser des règles métier soit produire un résultat
visuellement approximatif. Un écran, une validation, le suivant.

## 10. Ce que je te conseille de ne pas faire

- Ne pas lancer plusieurs de ces prompts en parallèle sur des branches différentes sans
  relire — le parcours de vente (Prompt 3) touche le fichier le plus central de l'app, il
  doit être fait seul, testé, validé, avant les autres.
- Ne pas ajouter de nouvelles colonnes/migrations "au cas où" pendant ces sprints UX — tout
  ce document tient volontairement sans modification de schéma, sauf la migration `phone`/
  `pin_hash` explicitement mentionnée en 5.1, qui doit être validée séparément.
- Ne pas faire valider ce document uniquement par toi — fais-le lire à quelqu'un qui
  ressemble vraiment à ton persona "vendeur", même en version papier/maquette, avant
  d'investir le développement complet du Prompt 3.
