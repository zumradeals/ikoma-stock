<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Devise par défaut
    |--------------------------------------------------------------------------
    |
    | Toutes les entreprises créées sur la plateforme utilisent le Franc CFA
    | (XOF) par défaut. Les montants sont stockés en centimes (entiers) dans
    | toutes les tables métier — diviser par ce facteur pour l'affichage.
    |
    */

    'currency' => env('IKOMA_CURRENCY', 'XOF'),

    'money_divisor' => 100,

];
