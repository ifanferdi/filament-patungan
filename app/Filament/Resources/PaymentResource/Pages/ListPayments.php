<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('updated_at', 'desc')
            ->query(
                (auth()->user()->username !== 'admin')
                    ? $this->getModel()::where('user_id', auth()->user()->id)
                    : $this->getModel()
            )
            ->columns([
                TextColumn::make('#')->rowIndex(),
                TextColumn::make('provider_label')
                    ->label(__('custom.provider')),
                TextColumn::make('account_number')
                    ->label(__('custom.account_number')),
                IconColumn::make('is_primary')
                    ->label(__('custom.is_primary') . '?')
                    ->boolean(),
                TextColumn::make('user.name')
                    ->label(__('filament-panels::pages/auth/register.form.name.label'))
                    ->hidden(fn () => auth()->user()->username !== 'admin'),
                TextColumn::make('created_at')
                    ->label(__('custom.created'))
                    ->since()
                    ->dateTimeTooltip(),
            ])->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('custom.add_payment'))
                ->action(function (array $data): void {
                    if (auth()->user()->username !== 'admin')
                        $data['user_id'] = auth()->user()->id;
                    $this->getModel()::create($data);
                }),
        ];
    }
}
