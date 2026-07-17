<div class="p-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">Paramètres plateforme</h1>
        <a href="{{ route('platform.index') }}" wire:navigate class="text-xs text-indigo-600 font-medium">← Sociétés</a>
    </div>

    @if ($saved)
        <div class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            Paramètres enregistrés.
        </div>
    @endif

    <form wire:submit="save" class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Envoi d'emails (SMTP)</p>
        <p class="text-xs text-gray-400">
            Tant que ces champs sont vides, les emails système (réinitialisation de mot de passe...)
            sont seulement écrits dans les logs du serveur, jamais envoyés réellement.
        </p>

        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
                <x-input-label value="Serveur SMTP (host)" />
                <x-text-input wire:model="mailHost" type="text" placeholder="smtp.exemple.com" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('mailHost')" class="mt-1" />
            </div>
            <div>
                <x-input-label value="Port" />
                <x-text-input wire:model="mailPort" type="number" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('mailPort')" class="mt-1" />
            </div>
            <div>
                <x-input-label value="Chiffrement" />
                <select wire:model="mailEncryption" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="">Aucun</option>
                </select>
            </div>
        </div>

        <div>
            <x-input-label value="Utilisateur SMTP" />
            <x-text-input wire:model="mailUsername" type="text" class="block mt-1 w-full" />
        </div>

        <div>
            <x-input-label value="Mot de passe SMTP" />
            <x-text-input wire:model="mailPassword" type="password" placeholder="Laisser vide pour conserver l'actuel" class="block mt-1 w-full" />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label value="Adresse d'expédition" />
                <x-text-input wire:model="mailFromAddress" type="email" placeholder="noreply@exemple.com" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('mailFromAddress')" class="mt-1" />
            </div>
            <div>
                <x-input-label value="Nom d'expédition" />
                <x-text-input wire:model="mailFromName" type="text" placeholder="IKOMA STOCK" class="block mt-1 w-full" />
            </div>
        </div>

        <x-primary-button type="submit" class="w-full justify-center">
            Enregistrer
        </x-primary-button>
    </form>
</div>
