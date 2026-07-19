<div class="flex items-center justify-between">
    <h1 class="text-base font-extrabold text-ink">Paramètres plateforme</h1>
    <a href="{{ route('platform.index') }}" wire:navigate class="text-xs text-brand font-bold">← Sociétés</a>
</div>

@if ($saved)
    <div class="rounded-xl border border-success/30 bg-success/5 px-4 py-3 text-sm font-bold text-success">
        Paramètres enregistrés.
    </div>
@endif

{{-- ── Section Identité de l'application ── --}}
<div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-4">
    <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Identité de l'application</p>

    {{-- Logo actuel --}}
    @if ($currentLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($currentLogoPath))
        <div class="flex items-center gap-4">
            <img src="{{ Storage::url($currentLogoPath) }}"
                 alt="Logo actuel"
                 class="h-12 w-auto rounded-xl border border-line object-contain bg-cream p-1">
            <button type="button" wire:click="removeLogo"
                    class="text-xs font-bold text-danger hover:underline">
                Supprimer le logo
            </button>
        </div>
    @endif

    {{-- Upload logo --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft mb-1">
            Logo {{ $currentLogoPath ? '(remplacer)' : '' }}
        </label>
        <input type="file" wire:model="appLogo" accept="image/*"
               class="block w-full text-sm text-ink-soft
                      file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0
                      file:text-xs file:font-bold file:bg-cream file:text-ink
                      hover:file:bg-line">
        <p class="text-xs text-ink-soft/60 mt-1">PNG, JPG, SVG — max 2 Mo</p>
        @error('appLogo') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Nom de l'application --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft mb-1">Nom de l'application</label>
        <input type="text" wire:model="appName"
               placeholder="{{ \App\Models\PlatformSetting::DEFAULT_APP_NAME }}"
               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
        @error('appName') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Tagline --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft mb-1">Tagline</label>
        <input type="text" wire:model="appTagline"
               placeholder="{{ \App\Models\PlatformSetting::DEFAULT_APP_TAGLINE }}"
               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
        @error('appTagline') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
    </div>
</div>

{{-- ── Section SMTP ── --}}
<div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-4">
    <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Envoi d'emails (SMTP)</p>
    <p class="text-xs text-ink-soft/70">
        Tant que ces champs sont vides, les emails système sont seulement écrits dans les logs du serveur.
    </p>

    <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
            <label class="block text-xs font-bold text-ink-soft mb-1">Serveur SMTP (host)</label>
            <input type="text" wire:model="mailHost" placeholder="smtp.exemple.com"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            @error('mailHost') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Port</label>
            <input type="number" wire:model="mailPort"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            @error('mailPort') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Chiffrement</label>
            <select wire:model="mailEncryption"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="">Aucun</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-ink-soft mb-1">Utilisateur SMTP</label>
        <input type="text" wire:model="mailUsername"
               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
    </div>

    <div>
        <label class="block text-xs font-bold text-ink-soft mb-1">Mot de passe SMTP</label>
        <input type="password" wire:model="mailPassword" placeholder="Laisser vide pour conserver l'actuel"
               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Adresse d'expédition</label>
            <input type="email" wire:model="mailFromAddress" placeholder="noreply@exemple.com"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
            @error('mailFromAddress') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Nom d'expédition</label>
            <input type="text" wire:model="mailFromName" placeholder="Ikoma Stock"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
        </div>
    </div>
</div>

{{-- Bouton enregistrer --}}
<button type="button" wire:click="save" wire:loading.attr="disabled"
        class="w-full rounded-2xl bg-brand text-white text-sm font-extrabold py-3.5 hover:brightness-90 active:brightness-75 transition disabled:opacity-60">
    <span wire:loading.remove wire:target="save">Enregistrer</span>
    <span wire:loading wire:target="save">Enregistrement…</span>
</button>
