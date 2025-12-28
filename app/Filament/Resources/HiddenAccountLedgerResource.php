<?php
namespace App\Filament\Resources;

use App\Models\Account_ledger;
use Filament\Resources\Resource;

class HiddenAccountLedgerResource extends Resource
{
    protected static ?string $model = Account_ledger::class;

    // Hide it from navigation so it doesn't clutter your sidebar
    protected static bool $shouldRegisterNavigation = false; 

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema;
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AccountLedgers\Pages\ListAccountLedgers::route('/'),
        ];
    }
}