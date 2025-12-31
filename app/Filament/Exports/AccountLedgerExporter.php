<?php

// namespace App\Filament\Exports;

// use App\Models\AccountLedger;
// use Filament\Actions\Exports\ExportColumn;
// use Filament\Actions\Exports\Exporter;
// use Filament\Actions\Exports\Models\Export;
// use Illuminate\Support\Number;

// class AccountLedgerExporter extends Exporter
// {
//     protected static ?string $model = AccountLedger::class;

//     public static function getColumns(): array
//     {
//         return [
//             //
//         ];
//     }

//     public static function getCompletedNotificationBody(Export $export): string
//     {
//         $body = 'Your account ledger export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

//         if ($failedRowsCount = $export->getFailedRowsCount()) {
//             $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
//         }

//         return $body;
//     }
// }

namespace App\Filament\Exports;

use App\Models\Account_ledger;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

use OpenSpout\Common\Entity\Row;

use Illuminate\Support\Number;

class AccountLedgerExporter extends Exporter
{
    protected static ?string $model = Account_ledger::class;

    public function getXlsxHeaderCellStyle(): ?Style
{
    return (new Style())
        ->setFontBold()
        ->setFontItalic()
        ->setFontSize(14)
        ->setFontName('Consolas')
        ->setFontColor(Color::rgb(255, 255, 77))
        ->setBackgroundColor(Color::rgb(0, 0, 0))
        ->setCellAlignment(CellAlignment::CENTER)
        ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
}
public function makeXlsxRow(array $values, ?Style $style = null): Row
{
    return Row::fromValues($values, $style);
}

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date_confirm')
                ->label('Date'),
            ExportColumn::make('reference_no')
                ->label('Reference'),
            ExportColumn::make('description'),
            ExportColumn::make('debit'),
            ExportColumn::make('credit'),
            ExportColumn::make('currencyInfo.currency_name')
                ->label('Currency'),
            ExportColumn::make('status'),
            // Note: Running balance is difficult to export directly via query,
            // but you can export the raw data for users to calculate in Excel.
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your account ledger export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
