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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-orange-50/40 text-gray-900" style="--brand: {{ auth()->user()->company?->primary_color ?: '#ea580c' }};">
        @include('layouts.partials.top-bar')

        <main class="pb-20">
            @if (isset($header))
                <header class="px-4 py-3 bg-white border-b border-gray-100">
                    {{ $header }}
                </header>
            @endif

            {{ $slot }}
        </main>

        @include('layouts.partials.bottom-nav')

        <livewire:components.confirmation-modal />

        @livewireScripts
    </body>
</html>
