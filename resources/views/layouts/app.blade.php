<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ea580c">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="IKOMA STOCK">
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <title>IKOMA STOCK</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    @php
        $brandHex  = auth()->user()->company?->primary_color ?: '#ea580c';
        $brandDark = brand_dark($brandHex);
        $brandWash = brand_wash($brandHex);
    @endphp
    <body class="font-sans antialiased bg-orange-50/40 text-gray-900"
          style="--brand:{{ $brandHex }};--brand-dark:{{ $brandDark }};--brand-wash:{{ $brandWash }};">

        {{-- ── Bandeau mode support (impersonation) ── --}}
        @if (session('impersonating_original_id'))
            <div class="sticky top-0 z-50 flex items-center justify-between gap-2 bg-gold px-4 py-2 text-xs font-extrabold text-white shadow">
                <span>⚠ Mode support — vous êtes connecté en tant que <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->company?->name }})</span>
                <form method="POST" action="{{ route('support.stop') }}" class="shrink-0">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-white/50 px-3 py-1 text-xs font-extrabold text-white hover:bg-white/20 transition">
                        Revenir à mon compte
                    </button>
                </form>
            </div>
        @endif

        <div class="{{ ($bareDesktop ?? false) ? 'lg:hidden' : '' }}">
            @include('layouts.partials.top-bar')
        </div>

        <main class="{{ ($bareDesktop ?? false) ? 'pb-20 lg:pb-0' : 'pb-20' }}">
            @if (isset($header))
                <header class="px-4 py-3 bg-white border-b border-gray-100">
                    {{ $header }}
                </header>
            @endif

            {{ $slot }}
        </main>

        <div class="{{ ($bareDesktop ?? false) ? 'lg:hidden' : '' }}">
            @include('layouts.partials.bottom-nav')
        </div>

        <livewire:components.confirmation-modal />

        @livewireScripts
    </body>
</html>
