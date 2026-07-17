@props(['status'])

@php
    // Mapping statut → [icône, libellé, classes Tailwind]
    // Les valeurs de `status` sont celles retournées par SaleStatusPresenter::resolve().
    $map = [
        'paid_delivered' => ['icon' => '✅', 'label' => 'Payé et livré',      'class' => 'bg-success-wash text-success'],
        'to_deliver'     => ['icon' => '📦', 'label' => 'Payé — à livrer',   'class' => 'bg-info-wash text-info'],
        'partial'        => ['icon' => '💰', 'label' => 'Reste à payer',      'class' => 'bg-gold-wash text-gold'],
        'free'           => ['icon' => '🎁', 'label' => 'Offert',             'class' => 'bg-info-wash text-info'],
        'unpaid'         => ['icon' => '⏳', 'label' => 'Non payé',           'class' => 'bg-gold-wash text-gold'],
        'cancelled'      => ['icon' => '❌', 'label' => 'Annulée',            'class' => 'bg-danger-wash text-danger'],
    ];

    $config = $map[$status] ?? $map['unpaid'];
@endphp

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 self-start rounded-pill px-2.5 py-1 text-[11px] font-extrabold ' . $config['class']]) }}
>
    {{ $config['icon'] }} {{ $config['label'] }}
</span>
