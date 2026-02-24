<?php

namespace App\Filament\Resources\GroupBookings\Tables;

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

use Filament\Tables\Table;
use Filament\Tables\Enums\ColumnManagerResetActionPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;

use Filament\Forms\Components\Select;

use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\User;
use Filament\Notifications\Notification;

class GroupBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                // TextColumn::make('reference_no'),
                 TextColumn::make('user.name')
                ->label('Inserted')
                ->description(fn ($record): string => $record->created_at?->format('M d, Y H:i') ?? 'N/A')
                ->searchable(),

            TextColumn::make('account.account_name')
                    ->label('Account')
                    // This replaces the "Ahmad" with the "Ahmad - Category (Branch)" version
                    ->formatStateUsing(fn ($record) => $record->account?->account_name_with_category_and_branch ?? 'N/A')
                    ->searchable()
                    ->sortable(),

                 TextColumn::make('type')->label('Flight Type'),

            TextColumn::make('adult_seat')
                ->label('Adult Fare')
                // Main line: Label + Seat
                ->formatStateUsing(fn ($record) => 'Seat(s): ' . ($record->adult_seat ?? 'N/A'))
                // Second line: Price breakdown
                ->description(function ($record) {
                    $base = number_format($record->adult_basefare ?? 0, 2);
                    $tax = number_format($record->adult_tax ?? 0, 2);
                    $total = number_format($record->adult_tprice ?? 0, 2);
                    $currency = $record->currencyInfo?->currency_code ?? '';
                    
                    return "Base: {$base} + Tax: {$tax} = {$total} {$currency}";
                })
                ->searchable(),

                  TextColumn::make('child_seat')
                ->label('Child Fare')
                // Main line: Label + Seat
                ->formatStateUsing(fn ($record) => 'Seat: ' . ($record->child_seat ?? 'N/A'))
                // Second line: Price breakdown
                ->description(function ($record) {
                    $base = number_format($record->child_basefare ?? 0, 2);
                    $tax = number_format($record->child_tax ?? 0, 2);
                    $total = number_format($record->child_tprice ?? 0, 2);
                    $currency = $record->currencyInfo?->currency_code ?? '';
                    
                    return "Base: {$base} + Tax: {$tax} = {$total} {$currency}";
                })
                ->searchable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('infant_seat')
                ->label('Infant Fare')
                // Main line: Label + Seat
                ->formatStateUsing(fn ($record) => 'Seat: ' . ($record->infant_seat ?? 'N/A'))
                // Second line: Price breakdown
                ->description(function ($record) {
                    $base = number_format($record->infant_basefare ?? 0, 2);
                    $tax = number_format($record->infant_tax ?? 0, 2);
                    $total = number_format($record->infant_tprice ?? 0, 2);
                    $currency = $record->currencyInfo?->currency_code ?? '';
                    
                    return "Base: {$base} + Tax: {$tax} = {$total} {$currency}";
                })
                ->searchable()->toggleable(isToggledHiddenByDefault: true),

               TextColumn::make('group_flights_details')
                ->label('Flight Details')
                ->getStateUsing(function ($record) {
                    // We use 'groupFlights' relationship from GroupBooking
                    return $record->groupFlights->map(function ($flight) {
                        $airlines = $flight->airlines ?? 'Unknown Airline'; //
                        $flightNo = $flight->flightno ?? 'TBA'; //
                        $pnr = $flight->pnr ? "({$flight->pnr})" : ''; //
                        
                        // Look up airport names using the relationship we added to GroupFlight
                        $fromName = $flight->fromAirport ? $flight->fromAirport->cityCode : $flight->from_f;
                        $toName = $flight->toAirport ? $flight->toAirport->cityCode : $flight->to_f;
                        
                        $from = "{$fromName} ({$flight->f_terminal})";
                        $to = "{$toName} ({$flight->t_terminal})";
                        $depart = $flight->depart_time ? $flight->depart_time->format('M d, Y H:i') : 'TBA';
                        $arrival = $flight->arrival_time ? $flight->arrival_time->format('M d, Y H:i') : 'TBA';

                        // We return a string with a <br> tag
                        return "<strong> {$pnr}</strong><br> {$airlines} {$flightNo} - {$from} ➔ {$to}<br>Depart: {$depart}, Arrive: {$arrival}";
                    })->implode('<hr class="my-2 border-gray-200">'); // Separate multiple flights with a line
                })
                ->html() // This is the crucial part that allows the <br> and <strong> tags to render
                ->searchable(query: function ($query, $search) {
                    $query->whereHas('groupFlights', function ($q) use ($search) {
                        $q->where('flightno', 'like', "%{$search}%")
                        ->orWhere('pnr', 'like', "%{$search}%")
                        ->orWhere('airlines', 'like', "%{$search}%");
                    });
                }),

                 TextColumn::make('update')->toggleable(isToggledHiddenByDefault: true),

            ])
         ->filters([
    Filter::make('flight_filter') // Renamed for clarity as it now filters time and status
        ->form([
            \Filament\Forms\Components\DatePicker::make('from')
                ->label('From'),
            \Filament\Forms\Components\DatePicker::make('until')
                ->label('To'),
            \Filament\Forms\Components\Select::make('status')
                ->label('Flight Status')
                ->options([
                    'Confirmed' => 'Confirmed',
                    'Pending'   => 'Pending',
                    'Cancelled' => 'Cancelled',
                ])
                ->placeholder('All Statuses'), // Allows users to see all if not selected
        ])
        ->query(function ($query, array $data) {
            return $query->when(
                $data['from'] || $data['until'] || $data['status'],
                fn ($query) => $query->whereHas('groupFlights', function ($q) use ($data) {
                    $q->when(
                        $data['from'],
                        fn ($q, $date) => $q->whereDate('depart_time', '>=', $date)
                    )
                    ->when(
                        $data['until'],
                        fn ($q, $date) => $q->whereDate('depart_time', '<=', $date)
                    )
                    ->when(
                        $data['status'],
                        fn ($q, $status) => $q->where('status', $status)
                    );
                })
            );
        })
        ->indicateUsing(function (array $data): array {
            $indicators = [];
            
            if ($data['from'] ?? null) {
                $indicators['from'] = 'Flights from ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
            }
            if ($data['until'] ?? null) {
                $indicators['until'] = 'Flights until ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
            }
            if ($data['status'] ?? null) {
                $indicators['status'] = 'Status: ' . $data['status'];
            }
            
            return $indicators;
        }),

    TrashedFilter::make(),
])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
