<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>


    @if(filled($this->data['selectedAccount'] ?? null))
    
@php
    $account = \App\Models\Accounts::where('id', $this->data['selectedAccount'] )->first();
    @endphp
        <x-filament::section class="mt-6">
            @include('filament.components.account-balance-table', ['accountUid' => $account->uid])
        </x-filament::section>
    @endif
</x-filament-panels::page>

