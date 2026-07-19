<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ea580c">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <link rel="manifest" href="/manifest.json">

        @php $platformSettings = \App\Models\PlatformSetting::current(); @endphp
        <title>{{ $platformSettings->resolvedAppName() }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    @php
        $brandHex   = '#ea580c';
        $brandDark  = brand_dark($brandHex);
        $brandWash  = brand_wash($brandHex);
        $bareHeader = $bareHeader ?? false;
    @endphp
    <body class="font-sans text-gray-900 antialiased"
          style="--brand:{{ $brandHex }};--brand-dark:{{ $brandDark }};--brand-wash:{{ $brandWash }};">
        <div class="min-h-screen flex flex-col sm:justify-center items-center {{ $bareHeader ? 'pt-4' : 'pt-10' }} sm:pt-0 px-4 bg-gradient-to-b from-orange-50 via-white to-white">

            @unless ($bareHeader)
                {{-- En-tête plateforme : masqué sur les pages ayant leur propre en-tête (ex. login) --}}
                <div class="text-center mb-6">
                    @if ($platformSettings->app_logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($platformSettings->app_logo_path))
                        <img src="{{ Storage::url($platformSettings->app_logo_path) }}"
                             alt="{{ $platformSettings->resolvedAppName() }}"
                             class="mx-auto h-14 w-auto mb-3 object-contain">
                    @else
                        <a href="/" wire:navigate class="inline-block">
                            <x-application-logo />
                        </a>
                    @endif
                    <p class="mt-3 text-lg font-semibold text-gray-800">{{ $platformSettings->resolvedAppName() }}</p>
                    <p class="text-sm text-gray-500">{{ $platformSettings->resolvedAppTagline() }}</p>
                </div>
            @endunless

            <div class="w-full sm:max-w-md {{ $bareHeader ? 'mt-2' : '' }} px-6 py-6 bg-white shadow-lg shadow-orange-100 overflow-hidden rounded-3xl border border-orange-100">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
