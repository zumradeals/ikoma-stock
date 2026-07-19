<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="platform-settings" />
    <div class="flex-1 overflow-y-auto">
        <div class="p-6 max-w-2xl space-y-6">
            @include('livewire.platform._settings-body')
        </div>
    </div>
</div>
{{-- Mobile --}}
<div class="lg:hidden">
    <div class="p-4 space-y-4">
        @include('livewire.platform._settings-body')
    </div>
</div>
</div>
