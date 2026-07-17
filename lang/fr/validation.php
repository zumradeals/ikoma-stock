<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Messages de validation
    |--------------------------------------------------------------------------
    |
    | Ces messages s'affichent quand un formulaire n'est pas rempli
    | correctement. Ils doivent rester simples et compréhensibles par
    | tous, y compris les personnes peu familières avec l'informatique.
    |
    */

    'accepted' => 'Vous devez accepter le champ :attribute.',
    'accepted_if' => 'Vous devez accepter le champ :attribute quand :other vaut :value.',
    'active_url' => 'Le champ :attribute doit être une adresse internet valide.',
    'after' => 'Le champ :attribute doit être une date après le :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date égale ou après le :date.',
    'alpha' => 'Le champ :attribute ne peut contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne peut contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num' => 'Le champ :attribute ne peut contenir que des lettres et des chiffres.',
    'any_of' => 'Le champ :attribute n\'est pas valide.',
    'array' => 'Le champ :attribute doit être une liste.',
    'ascii' => 'Le champ :attribute ne peut contenir que des caractères et symboles simples.',
    'before' => 'Le champ :attribute doit être une date avant le :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date égale ou avant le :date.',
    'between' => [
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file' => 'Le champ :attribute doit peser entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'can' => 'Le champ :attribute contient une valeur non autorisée.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'contains' => 'Le champ :attribute doit contenir une valeur requise.',
    'current_password' => 'Le mot de passe saisi est incorrect.',
    'date' => 'Le champ :attribute doit être une date valide.',
    'date_equals' => 'Le champ :attribute doit être une date égale au :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'decimal' => 'Le champ :attribute doit avoir :decimal décimales.',
    'declined' => 'Vous devez refuser le champ :attribute.',
    'declined_if' => 'Vous devez refuser le champ :attribute quand :other vaut :value.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions' => 'Les dimensions de l\'image du champ :attribute ne sont pas valides.',
    'distinct' => 'Le champ :attribute contient une valeur en double.',
    'doesnt_contain' => 'Le champ :attribute ne doit pas contenir : :values.',
    'doesnt_end_with' => 'Le champ :attribute ne doit pas se terminer par : :values.',
    'doesnt_start_with' => 'Le champ :attribute ne doit pas commencer par : :values.',
    'email' => 'Le champ :attribute doit être une adresse email valide (exemple : nom@exemple.com).',
    'encoding' => 'Le champ :attribute doit être encodé en :encoding.',
    'ends_with' => 'Le champ :attribute doit se terminer par l\'une des valeurs suivantes : :values.',
    'enum' => 'La valeur choisie pour :attribute n\'est pas valide.',
    'exists' => 'La valeur choisie pour :attribute n\'existe pas.',
    'extensions' => 'Le champ :attribute doit avoir l\'une des extensions suivantes : :values.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit contenir une valeur.',
    'gt' => [
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file' => 'Le champ :attribute doit peser plus de :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte' => [
        'array' => 'Le champ :attribute doit contenir :value éléments ou plus.',
        'file' => 'Le champ :attribute doit peser :value kilo-octets ou plus.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],
    'hex_color' => 'Le champ :attribute doit être une couleur valide.',
    'image' => 'Le champ :attribute doit être une image (photo).',
    'in' => 'La valeur choisie pour :attribute n\'est pas valide.',
    'in_array' => 'Le champ :attribute doit exister dans :other.',
    'in_array_keys' => 'Le champ :attribute doit contenir au moins l\'une des valeurs suivantes : :values.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4' => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json' => 'Le champ :attribute doit être un texte JSON valide.',
    'list' => 'Le champ :attribute doit être une liste.',
    'lowercase' => 'Le champ :attribute doit être écrit en minuscules.',
    'lt' => [
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file' => 'Le champ :attribute doit peser moins de :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],
    'lte' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
        'file' => 'Le champ :attribute doit peser :value kilo-octets ou moins.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au maximum :value caractères.',
    ],
    'mac_address' => 'Le champ :attribute doit être une adresse MAC valide.',
    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file' => 'Le champ :attribute ne doit pas peser plus de :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'max_digits' => 'Le champ :attribute ne doit pas contenir plus de :max chiffres.',
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le champ :attribute doit peser au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'min_digits' => 'Le champ :attribute doit contenir au moins :min chiffres.',
    'missing' => 'Le champ :attribute doit être absent.',
    'missing_if' => 'Le champ :attribute doit être absent quand :other vaut :value.',
    'missing_unless' => 'Le champ :attribute doit être absent sauf si :other vaut :value.',
    'missing_with' => 'Le champ :attribute doit être absent quand :values est présent.',
    'missing_with_all' => 'Le champ :attribute doit être absent quand :values sont présents.',
    'multiple_of' => 'Le champ :attribute doit être un multiple de :value.',
    'not_in' => 'La valeur choisie pour :attribute n\'est pas valide.',
    'not_regex' => 'Le format du champ :attribute n\'est pas valide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => [
        'letters' => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed' => 'Le champ :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols' => 'Le champ :attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Ce :attribute a été trouvé dans une fuite de données connue. Merci d\'en choisir un autre.',
    ],
    'present' => 'Le champ :attribute doit être présent.',
    'present_if' => 'Le champ :attribute doit être présent quand :other vaut :value.',
    'present_unless' => 'Le champ :attribute doit être présent sauf si :other vaut :value.',
    'present_with' => 'Le champ :attribute doit être présent quand :values est présent.',
    'present_with_all' => 'Le champ :attribute doit être présent quand :values sont présents.',
    'prohibited' => 'Le champ :attribute n\'est pas autorisé.',
    'prohibited_if' => 'Le champ :attribute n\'est pas autorisé quand :other vaut :value.',
    'prohibited_if_accepted' => 'Le champ :attribute n\'est pas autorisé quand :other est accepté.',
    'prohibited_if_declined' => 'Le champ :attribute n\'est pas autorisé quand :other est refusé.',
    'prohibited_unless' => 'Le champ :attribute n\'est pas autorisé sauf si :other est parmi : :values.',
    'prohibits' => 'Le champ :attribute empêche :other d\'être présent.',
    'regex' => 'Le format du champ :attribute n\'est pas valide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_array_keys' => 'Le champ :attribute doit contenir une entrée pour : :values.',
    'required_if' => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_if_accepted' => 'Le champ :attribute est obligatoire quand :other est accepté.',
    'required_if_declined' => 'Le champ :attribute est obligatoire quand :other est refusé.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other est parmi : :values.',
    'required_with' => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire quand :values sont présents.',
    'required_without' => 'Le champ :attribute est obligatoire quand :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire quand aucun de :values n\'est présent.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'size' => [
        'array' => 'Le champ :attribute doit contenir :size éléments.',
        'file' => 'Le champ :attribute doit peser :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit être égal à :size.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with' => 'Le champ :attribute doit commencer par l\'une des valeurs suivantes : :values.',
    'string' => 'Le champ :attribute doit être du texte.',
    'timezone' => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique' => 'Cette valeur pour :attribute est déjà utilisée.',
    'uploaded' => 'L\'envoi du fichier :attribute a échoué. Vérifiez sa taille.',
    'uppercase' => 'Le champ :attribute doit être écrit en majuscules.',
    'url' => 'Le champ :attribute doit être une adresse internet valide.',
    'ulid' => 'Le champ :attribute doit être un ULID valide.',
    'uuid' => 'Le champ :attribute doit être un UUID valide.',

    /*
    |--------------------------------------------------------------------------
    | Messages personnalisés
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'admin_password' => [
            'min' => 'Le mot de passe doit contenir au moins :min caractères.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Noms de champs en français simple
    |--------------------------------------------------------------------------
    |
    | Ces noms remplacent les noms techniques des champs (ex. "email")
    | par des mots simples et clairs pour tous les utilisateurs.
    |
    */

    'attributes' => [
        // Général
        'name' => 'le nom',
        'email' => 'adresse email',
        'password' => 'mot de passe',
        'phone' => 'numéro de téléphone',
        'address' => 'adresse',
        'notes' => 'note',
        'reason' => 'raison',
        'reference' => 'référence',
        'method' => 'mode de paiement',
        'amount' => 'montant',
        'currency' => 'devise',
        'role' => 'rôle',

        // Installation
        'app_name' => 'nom de l\'application',
        'app_url' => 'adresse du site',
        'db_host' => 'serveur de la base de données',
        'db_port' => 'port de la base de données',
        'db_database' => 'nom de la base de données',
        'db_username' => 'utilisateur de la base de données',
        'db_password' => 'mot de passe de la base de données',
        'admin_name' => 'nom',
        'admin_email' => 'email',
        'admin_password' => 'mot de passe',
        'adminName' => 'nom',
        'adminEmail' => 'email',

        // Entreprise / paramètres
        'companyName' => 'nom de l\'entreprise',
        'companyAddress' => 'adresse de l\'entreprise',
        'companyPhone' => 'téléphone de l\'entreprise',
        'companyEmail' => 'email de l\'entreprise',
        'companyCurrency' => 'devise',
        'companyInvoicePrefix' => 'préfixe des factures',
        'companyFooterText' => 'texte de bas de facture',
        'companyLogo' => 'logo',
        'primary_color' => 'couleur principale',
        'mailHost' => 'serveur d\'envoi des emails',
        'mailPort' => 'port d\'envoi des emails',
        'mailUsername' => 'utilisateur d\'envoi des emails',
        'mailPassword' => 'mot de passe d\'envoi des emails',
        'mailFromAddress' => 'adresse d\'envoi des emails',
        'mailFromName' => 'nom d\'envoi des emails',

        // Utilisateurs
        'userName' => 'nom de l\'utilisateur',

        // Points de vente / dépôts
        'outletName' => 'nom du point de vente',
        'outletAddress' => 'adresse du point de vente',
        'outletPhone' => 'téléphone du point de vente',
        'warehouseName' => 'nom du dépôt',
        'warehouseAddress' => 'adresse du dépôt',
        'outlet_id' => 'point de vente',
        'warehouse_id' => 'dépôt',
        'locationId' => 'emplacement',
        'sourceWarehouseId' => 'dépôt de départ',
        'destinationOutletId' => 'point de vente de destination',

        // Produits
        'productName' => 'nom du produit',
        'categoryName' => 'catégorie',
        'productReference' => 'référence du produit',
        'productCostPrice' => 'prix d\'achat',
        'productSalePrice' => 'prix de vente',
        'productLowStockThreshold' => 'seuil de stock bas',
        'productImage' => 'photo du produit',
        'initialStockQuantity' => 'quantité en stock',
        'product_id' => 'produit',
        'productId' => 'produit',
        'category_id' => 'catégorie',

        // Clients
        'customer_id' => 'client',
        'customer_type' => 'type de client',
        'creditLimit' => 'limite de crédit',
        'neighborhoodCity' => 'quartier ou ville',

        // Ventes / paiements
        'cart' => 'panier',
        'proof' => 'preuve de paiement',
        'proof_path' => 'preuve de paiement',
        'cancelReason' => 'raison de l\'annulation',
        'rejectReason' => 'raison du refus',

        // Stock
        'countedQuantity' => 'quantité comptée',
        'delta' => 'écart',
        'quantity' => 'quantité',
        'unit_price' => 'prix unitaire',
        'unit' => 'unité',
    ],

];
