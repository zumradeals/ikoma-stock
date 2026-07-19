<x-app-layout :bareDesktop="true">
<div class="lg:flex lg:h-screen lg:overflow-hidden">

    {{-- ════ Sidebar desktop ════ --}}
    <div class="hidden lg:flex">
        <x-ikoma.desktop-sidebar active="profile" />
    </div>

    {{-- ════ Contenu principal ════ --}}
    <div class="flex-1 lg:overflow-y-auto">
        <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 lg:px-5">
            <h1 class="text-base font-extrabold text-ink">{{ __('Profile') }}</h1>
        </div>

        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 lg:px-0 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
