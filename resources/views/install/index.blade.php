<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ea580c">
        <title>Installation — IKOMA STOCK</title>
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

            <div class="w-full sm:max-w-lg mt-6 px-6 py-6 bg-white shadow-lg shadow-orange-100 overflow-hidden rounded-3xl border border-orange-100">
                <div class="space-y-5">
                    <div class="text-center">
                        <h1 class="text-lg font-semibold text-gray-900">Installation d'IKOMA STOCK</h1>
                        <p class="text-sm text-gray-500 mt-1">Quelques informations et c'est parti.</p>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 space-y-1.5">
                        @foreach ($requirements as $label => $ok)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $label }}</span>
                                @if ($ok)
                                    <span class="text-green-600 font-medium">✓ OK</span>
                                @else
                                    <span class="text-red-600 font-medium">✕ Manquant</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @unless ($canInstall)
                        <p class="text-sm text-red-700 bg-red-50 rounded-xl p-3">
                            Certains prérequis ne sont pas satisfaits. Contactez votre hébergeur pour les activer avant de continuer.
                        </p>
                    @endunless

                    @if ($errors->any())
                        <div class="text-sm text-red-700 bg-red-50 rounded-xl p-3 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('install.store') }}" class="space-y-5">
                        @csrf

                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Votre boutique / entreprise</p>

                            <div>
                                <x-input-label value="Nom de l'application" />
                                <x-text-input name="app_name" type="text" value="{{ old('app_name', 'IKOMA STOCK') }}" class="block mt-1 w-full" required />
                            </div>

                            <div>
                                <x-input-label value="Adresse du site (URL)" />
                                <x-text-input name="app_url" type="url" value="{{ old('app_url', $appUrl) }}" class="block mt-1 w-full" required />
                                <p class="text-xs text-gray-400 mt-1">L'adresse complète par laquelle vos utilisateurs accèdent au site.</p>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-gray-100 pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Base de données</p>
                            <p class="text-xs text-gray-400">
                                Ces informations vous sont fournies par votre hébergeur (cPanel : section "Bases de données MySQL"). Créez d'abord la base et l'utilisateur MySQL depuis votre espace d'hébergement.
                            </p>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label value="Serveur (host)" />
                                    <x-text-input name="db_host" type="text" value="{{ old('db_host', 'localhost') }}" class="block mt-1 w-full" required />
                                </div>
                                <div>
                                    <x-input-label value="Port" />
                                    <x-text-input name="db_port" type="number" value="{{ old('db_port', '3306') }}" class="block mt-1 w-full" required />
                                </div>
                            </div>

                            <div>
                                <x-input-label value="Nom de la base" />
                                <x-text-input name="db_database" type="text" value="{{ old('db_database') }}" class="block mt-1 w-full" required />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label value="Utilisateur" />
                                    <x-text-input name="db_username" type="text" value="{{ old('db_username') }}" class="block mt-1 w-full" required />
                                </div>
                                <div>
                                    <x-input-label value="Mot de passe" />
                                    <x-text-input name="db_password" type="password" class="block mt-1 w-full" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-gray-100 pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Votre compte administrateur</p>
                            <p class="text-xs text-gray-400">Ce sera votre compte principal pour gérer toute la plateforme.</p>

                            <div>
                                <x-input-label value="Votre nom" />
                                <x-text-input name="admin_name" type="text" value="{{ old('admin_name') }}" class="block mt-1 w-full" required />
                            </div>

                            <div>
                                <x-input-label value="Votre email" />
                                <x-text-input name="admin_email" type="email" value="{{ old('admin_email') }}" class="block mt-1 w-full" required />
                            </div>

                            <div>
                                <x-input-label value="Mot de passe (8 caractères minimum)" />
                                <x-text-input name="admin_password" type="password" class="block mt-1 w-full" required minlength="8" />
                            </div>
                        </div>

                        <x-primary-button type="submit" class="w-full justify-center py-3 text-sm" :disabled="! $canInstall">
                            Installer IKOMA STOCK
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
