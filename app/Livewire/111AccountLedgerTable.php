<?php

namespace App\Livewire;

use App\Models\Account_ledger;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class AccountLedgerTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    public $record; // This will be the Account model

    public function table(Table $table): Table
    {
        return $table
            ->query(Account_ledger::query()->where('account', $this->record->uid))
            ->columns([
                Tables\Columns\TextColumn::make('date_confirm')->date()->sortable(),
                Tables\Columns\TextColumn::make('reference_no')->searchable()->fontFamily('mono'),
                Tables\Columns\TextColumn::make('description')->wrap(),
                Tables\Columns\TextColumn::make('credit')->numeric(2)->color('success')->alignEnd(),
                Tables\Columns\TextColumn::make('debit')->numeric(2)->color('danger')->alignEnd(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn($q) => $q->whereDate('date_confirm', '>=', $data['from']))
                        ->when($data['to'], fn($q) => $q->whereDate('date_confirm', '<=', $data['to']))),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        \Filament\Forms\Components\TextInput::make('description')->required(),
                        \Filament\Forms\Components\TextInput::make('credit')->numeric(),
                        \Filament\Forms\Components\TextInput::make('debit')->numeric(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public function render()
    {
        return view('livewire.account-ledger-table');
    }
}