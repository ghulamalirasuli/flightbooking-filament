<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
           
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                ->password()
                ->required()
                ->minLength(8)
                ->hiddenOn(Operation::Edit)
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create'),

                 TextInput::make('password_confirmation')
                ->password()
                ->required()
                ->same('password') // ✅ This is the key
                ->label('Confirm Password')
                 ->hiddenOn(Operation::Edit),
             
                FileUpload::make('photo')->image(),
                // Toggle::make('is_active')
                //     ->required()
                //     ->default(0),

                    TextInput::make('mobile_number')
                    ->label('Mobile No.'),

                   Textarea::make('address')
                    ->label('User Address')
                    ->trim()
                    ->rows(2),

 // -------1 Using select box-----------------------------------------------------------   
                Select::make('roles')
                    ->relationship('roles', 'name')
                    // ->disabled(! auth()->user()->can('assign_roles')) // Ensure only authorized users can touch this
                    ->multiple()
                    ->preload()
                    ->searchable(),
            // -------2 Using check box-----------------------------------------------------------   
                // CheckboxList::make('roles')
                //     ->relationship('roles', 'name')
                //     ->searchable(),
              
                    Select::make('branch_id') // Binds to the correct foreign key column
                    ->label('Branch')
                    ->options(
                        // Pluck 'name' (display) and 'id' (value to store)
                        \App\Models\Branch::query()
                            // Filter to only show active users
                            ->where('status', true)
                            ->pluck('branch_name', 'id')
                            ->toArray() 
                    )
                    ->searchable(),
            ]);
    }
}
