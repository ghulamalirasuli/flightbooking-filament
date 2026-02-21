<?php

namespace App\Filament\Resources\Providers;

use App\Filament\Resources\Providers\Pages\ManageProviders;
use App\Models\Provider;
use App\Models\Accounts;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use Filament\Actions\RestoreAction;        
use Filament\Actions\RestoreBulkAction;    
use Filament\Actions\ForceDeleteAction;    
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\BulkAction;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; //
use Filament\Schemas\Components\Utilities\Get; //

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Crypt;

use UnitEnum;
use BackedEnum;
use Filament\Notifications\Notification;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;
    protected static ?int $navigationSort = 8;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

/**
     * Scaling up the UI with custom CSS attributes
     */
    protected static function getBigStyles(): array
    {
        return [
            'style' => 'font-size: 1.25rem; height: 3.5rem; padding: 1rem; border-radius: 0.75rem;',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Step 1: Provider Identity')
                    ->description('Choose your supplier and the way you connect to them.')
                    ->schema([
                        // Select::make('account_uid')
                        //     ->label('Provider / Supplier')
                        //     ->options(Accounts::all()->pluck('account_name', 'uid'))
                        //     ->searchable()
                        //     ->required()
                        //     ->native(false)
                        //     ->extraAttributes(['style' => 'font-size: 1.25rem;'])->columnSpan(6),

                         Select::make('account_uid')
                                    ->label('Provider / Supplier')
                                    ->options(function (callable $get) {
                                        return Accounts::query()
                                            ->with(['accountType', 'branch']) // Eager load for performance
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($account) {
                                                $name = $account->account_name;
                                                $category = $account->accountType?->accounts_category ?? 'N/A';
                                                $branch = $account->branch?->branch_name ?? 'N/A';

                                                return [
                                                    $account->uid => "({$branch}) {$name} - {$category}",
                                                ];
                                            });
                                    })
                                    ->live()
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6),


                        Select::make('auth_type')
                            ->label('Authentication Method')
                            ->options([
                                'normal' => 'Normal (API Key / Secret)',
                                'session' => 'Session (Username / Password)',
                            ])
                            ->required()
                            ->live() // Essential for reactivity
                            ->native(false)
                            ->extraAttributes(['style' => 'font-size: 1.25rem;'])->columnSpan(6),
                    ])->columns(2),

                // Group 1: API (Normal)
                Section::make('Step 2: API Keys')
                    ->visible(fn (Get $get) => $get('auth_type') === 'normal')
                    ->schema([
                        TextInput::make('api_key')
                            ->label('Public API Key')
                            ->required()
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(6),
                        TextInput::make('api_secret')
                            ->label('Secret API Key')
                            ->password()
                            ->revealable()
                            ->required()
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(6),
                    ])->columns(2),

                // Group 2: Session
                Section::make('Step 2: Session Credentials')
                    ->visible(fn (Get $get) => $get('auth_type') === 'session')
                    ->schema([
                        TextInput::make('extra_config.AuthenticationKey')
                            ->label('OfficeId / Auth Key')
                            ->required()
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(6),
                        TextInput::make('extra_config.Username')
                            ->label('Account Username')
                            ->required()
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(6),
                        TextInput::make('extra_config.Password')
                            ->label('Account Password')
                            ->password() // Keep this so revealable() works
                            ->revealable() // This adds the eye icon to show/hide
                            ->required()
                            // Removed dehydrateStateUsing(fn ($state) => Crypt::encryptString($state))
                            // Now it will save as plain text "it as it is"
                            ->extraInputAttributes(static::getBigStyles())
                            ->columnSpan(6),
                        // TextInput::make('extra_config.Password') // Encript PW and store encrypted
                        //     ->label('Account Password')
                        //     ->password()
                        //     ->revealable()
                        //     ->required()
                        //     ->dehydrateStateUsing(fn ($state) => filled($state) ? Crypt::encryptString($state) : null)
                        //     ->extraInputAttributes(static::getBigStyles())->columnSpan(6),
                    ])->columns(3),

                Section::make('Step 3: API Endpoints')
                    ->schema([
                         Grid::make(12)
                            ->schema([
                        TextInput::make('base_url')
                            ->label('Base API URL')
                            ->url()
                            ->placeholder('https://api.provider.com')
                            ->required()
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(4),
                        TextInput::make('auth_endpoint')
                            ->label('Token / Auth Path')
                            ->placeholder('/oauth2/token')
                            ->required()
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(4),
                        

                        TextInput::make('url_endpoint')
                            ->label('Resource Path (Optional)')
                            ->placeholder('/v2/flight-search')
                            ->extraInputAttributes(static::getBigStyles())->columnSpan(4),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                   TextColumn::make('account.account_name')
                    ->label('Provider')
                    // This replaces the "Ahmad" with the "Ahmad - Category (Branch)" version
                    ->formatStateUsing(fn ($record) => $record->account?->account_name_with_category_and_branch ?? 'N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('auth_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'normal' => 'info',
                        'session' => 'warning',
                    }),
                TextColumn::make('base_url')
                    ->limit(30),

                 TextColumn::make('auth_endpoint')
                    ->limit(30),
                 TextColumn::make('url_endpoint')
                    ->limit(30),

                ToggleColumn::make('status') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->account->account_name} is now Active."
                        : "{$record->account->account_name} has been set to Inactive.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),
                
            TextColumn::make('extra_config')->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('extra_config')
                // ->label('Authentication Details')
                // ->html() // This allows the <br> tags to create new lines
                // ->formatStateUsing(function ($state, $record) {
                //     // If it's a 'normal' auth type, extra_config might be empty
                //     if ($record->auth_type !== 'session' || empty($state)) {
                //         return '<span class="text-gray-400">Standard API Keys</span>';
                //     }

                //     // Loop through the array and format as "Key = Value"
                //     return collect($state)
                //         ->map(function ($value, $key) {
                //             // Formatting the key for better readability (optional)
                //             return "<strong>{$key}</strong> = {$value}";
                //         })
                //         ->implode('<br>'); // Joins them with a line break
                // }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProviders::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}