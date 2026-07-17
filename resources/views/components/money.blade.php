@props(['amount', 'currency' => null])

{{ number_format(($amount ?? 0) / 100, 0, ',', ' ') }} {{ $currency ?? auth()->user()?->company?->currency }}
