<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1.0, user-scalable=no">
        <meta name="theme-color" content="#ea580c">
        <title>Installation terminée — IKOMA STOCK</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 px-4 bg-gradient-to-b from-orange-50 via-white to-white">
            <div class="text-center">
                <a href="/" class="inline-block">
                    <x-application-logo />
                </a>
                <p class="mt-3 text-lg font-semibold text-gray-800">IKOMA STOCK</p>
                <p class="text-sm text-gray-500">Votre boutique, simplement.</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white shadow-lg shadow-orange-100 overflow-hidden rounded-3xl border border-orange-100">
                <div class="text-center space-y-4 py-2">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-green-600 text-3xl">
                        ✓
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Installation terminée !</h1>
                        <p class="text-sm text-gray-500 mt-1">Votre plateforme IKOMA STOCK est prête.</p>
                    </div>
                    <a href="{{ $appUrl }}/login" class="block rounded-xl bg-[var(--brand,#ea580c)] text-white text-sm font-semibold py-3">
                        Se connecter
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
