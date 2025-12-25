
{{-- <x-filament-panels::page>
    <div class="fi-header bg-success-50 dark:bg-success-900/20 border-b border-success-200 dark:border-success-800 px-6 py-4 -mx-6 -mt-6 mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-success-700 dark:text-success-300">
                {{ $this->getTitle() }}
            </h1>

            <div class="flex items-center gap-3">
                @foreach ($this->getHeaderActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>
    </div>

    {{ $this->getInfolist() }}
</x-filament-panels::page> --}}
{{-- resources/views/filament/resources/accounts/pages/view-accounts.blade.php --}}
<x-filament-panels::page>
    <div class="fi-header bg-success-50 dark:bg-success-900/20 border-b border-success-200 dark:border-success-800 px-6 py-4 -mx-6 -mt-6 mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-success-700 dark:text-success-300">
                {{ $this->getTitle() }}
            </h1>

            <div class="flex items-center gap-3">
                @foreach ($this->getHeaderActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>
    </div>

    {{ $this->infolist($this->getInfolistSchema()) }}
</x-filament-panels::page>