@props(['amount', 'unit' => null])

{{ number_format(($amount ?? 0) / 100, 0, ',', ' ') }}@if ($unit) {{ is_string($unit) ? $unit : $unit->label() }}@endif
