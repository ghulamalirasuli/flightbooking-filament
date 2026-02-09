<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <x-filament::section class="mt-6">
        @include('filament.components.account-balance-filter-table')
    </x-filament::section>
</x-filament-panels::page>
