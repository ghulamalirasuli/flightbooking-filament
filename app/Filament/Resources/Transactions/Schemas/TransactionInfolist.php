<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\ContactInfo;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECTION 1: Add Contact (Modal Only)
                Section::make('Add New Contact')
                    ->icon(Heroicon::UserPlus)
                    // ->description('Click the button below to add a new contact to this transaction')
                    ->headerActions([  // Changed from footerActions to headerActions
                        Action::make('createContact')
                            ->label('Add Contact')
                            ->icon('heroicon-m-plus-circle')
                            ->color('success')
                            ->modalHeading('Add Contact')
                            ->modalWidth('4xl')
                            ->form([
                                Grid::make(12)->schema([
                                    TextInput::make('fullname')
                                        ->label('Fullname')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(4),

                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255)
                                        ->columnSpan(4),

                                    TextInput::make('mobile_number')
                                        ->label('Mobile Number')
                                        ->tel()
                                        ->maxLength(255)
                                        ->columnSpan(4),
                                ])->columnSpanFull(),
                            ])
                            ->action(function (array $data, $record) {
                                ContactInfo::create([
                                    'uid' => 'CNT'.now()->format('ymdHis').rand(10, 99),
                                    'reference_no' => $record->reference_no,
                                    'branch_id' => $record->branch_id,
                                    'user_id' => auth()->id(),
                                    'fullname' => $data['fullname'],
                                    'email' => $data['email'],
                                    'mobile_number' => $data['mobile_number'],
                                ]);

                                Notification::make()
                                    ->title('Contact added successfully')
                                    ->success()
                                    ->send();
                            })
                            ->after(function ($livewire, $record) {
                                $record->load('contactInfos');
                                $livewire->refresh();
                            }),
                    ])
                    ->schema([]) // No inline fields - empty schema
                    ->columnSpanFull(),

                // SECTION 2: Contact Details Table
                Section::make('Contact Details Table')
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->collapsible()
                    ->key('contact-table') // Add a unique key
                    ->visible(fn ($record) => $record->contactInfos()->count() > 0)
                    ->schema([
                        RepeatableEntry::make('contactInfos')
                            ->hiddenLabel()
                            ->contained(false)
                            ->columnSpanFull()
                            ->schema([
                                Section::make(fn ($record) => $record['fullname'] ?? 'Contact')
                                    ->collapsible(false)
                                    ->compact()
                                    ->headerActions([
                                        Action::make('edit')
                                            ->label('Edit')
                                            ->icon('heroicon-m-pencil-square')
                                            ->color('warning')
                                            ->modalHeading('Edit Contact')
                                            ->modalWidth('4xl')
                                            // Step 1: Fill the form INCLUDING the uid in a hidden field
                                            ->fillForm(fn ($record) => [
                                                'contact_uid' => $record['uid'], // Store UID in hidden field
                                                'fullname' => $record['fullname'],
                                                'email' => $record['email'],
                                                'mobile_number' => $record['mobile_number'],
                                            ])
                                            ->form([
                                                Grid::make(12)->schema([
                                                    // Hidden field to identify which contact to update
                                                    \Filament\Forms\Components\Hidden::make('contact_uid'),

                                                    TextInput::make('fullname')
                                                        ->label('Fullname')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->columnSpan(3),

                                                    TextInput::make('email')
                                                        ->label('Email')
                                                        ->email()
                                                        ->maxLength(255)
                                                        ->columnSpan(3),

                                                    TextInput::make('mobile_number')
                                                        ->label('Mobile Number')
                                                        ->tel()
                                                        ->maxLength(255)
                                                        ->columnSpan(3),

                                                ])->columnSpanFull(),
                                            ])
                                            // Step 2: Use the uid from form data ($data) instead of $record
                                            ->action(function (array $data, $record) {
                                                // Get UID from the hidden form field, not from $record
                                                $contactUid = $data['contact_uid'];

                                                // Remove the hidden field from update data
                                                unset($data['contact_uid']);

                                                // Update the specific contact
                                                ContactInfo::where('uid', $contactUid)->update($data);

                                                Notification::make()
                                                    ->title('Contact updated successfully')
                                                    ->success()
                                                    ->send();
                                            })
                                            ->after(function ($livewire) {
                                                $livewire->record->load('contactInfos');
                                                $livewire->refresh();
                                            }),

                                        Action::make('delete')
                                            ->label('Delete')
                                            ->icon('heroicon-m-trash')
                                            ->color('danger')
                                            ->requiresConfirmation()
                                            ->modalHeading('Delete Contact')
                                            ->action(function ($record) {
                                                ContactInfo::where('uid', $record['uid'])->delete();

                                                Notification::make()
                                                    ->title('Contact deleted successfully')
                                                    ->success()
                                                    ->send();
                                            })->after(function ($livewire) {  // Add this
                                                $livewire->record->load('contactInfos');
                                                $livewire->refresh();
                                            }),
                                    ])
                                    ->columns(12)
                                    ->schema([
                                            TextEntry::make('fullname')
                                                ->label('Fullname')
                                                ->weight(FontWeight::SemiBold)
                                                ->columnSpan(3),

                                            TextEntry::make('email')
                                                ->label('Email')
                                                ->icon('heroicon-m-envelope')
                                                ->copyable()
                                                ->columnSpan(3),

                                            TextEntry::make('mobile_number')
                                                ->label('Mobile Number')
                                                ->icon('heroicon-m-phone')
                                                ->copyable()
                                                ->columnSpan(3),
                                            TextEntry::make('inserted_by') // Change name to avoid dot notation confusion
                                            ->label('Inserted')
                                            ->state(fn ($record) => is_array($record) 
                                                ? ($record['user']['name'] ?? 'System')
                                                : ($record->user?->name ?? 'System')
                                            )
                                            ->formatStateUsing(function ($state, $record) {
                                                $createdAt = is_array($record) 
                                                    ? ($record['created_at'] ?? null) 
                                                    : $record->created_at;
                                                    
                                                $date = $createdAt 
                                                    ? \Carbon\Carbon::parse($createdAt)->format('M d, Y H:i') 
                                                    : '-';
                                                    
                                                return "{$state}<br><span style='color: #6b7280; font-size: 0.75rem;'>{$date}</span>";
                                            })
                                            ->html()
                                            ->columnSpan(3),

                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
