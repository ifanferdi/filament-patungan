<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('custom.add_order')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('#')->rowIndex(),
            TextColumn::make('name')->label(__('custom.order_name'))->searchable(),
            TextColumn::make('date')->label(__('custom.date'))->date('d F Y'),
            TextColumn::make('author.name')->label(__('custom.author'))->searchable(),
            TextColumn::make('details_count')
                ->label(__('custom.total_products'))
                ->counts('details')
                ->badge()
                ->suffix(' ' . Str::lower(__('custom.item'))),
            TextColumn::make('details_sum_final_price')
                ->label(__('custom.final_price'))
                ->badge()
                ->sum('details', 'final_price')
                ->money('IDR', locale: 'id'),
            TextColumn::make('details_unpaid_count')
                ->counts('details_unpaid')
                ->label(__('custom.is_paid'))
                ->badge()
                ->formatStateUsing(fn (string $state): string => $state > 0 ? $state . ' ' . __('custom.unpaid') : __('custom.all_paid'))
                ->color(fn (string $state): string => $state > 0 ? 'danger' : 'success')
                ->icon(fn (string $state): string => $state > 0 ? '' : 'heroicon-o-check-circle'),
            TextColumn::make('deleted_at')
                ->label(__('custom.trashed'))
                ->color('danger')
                ->formatStateUsing(fn (string $state): string => Carbon::parse($state)->diffForHumans())
                ->hidden(function ($livewire) {
                    return !isset($livewire->getTableFilterState('trashed')['value']) || $livewire->getTableFilterState('trashed')['value'] === '';
                }),
        ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('mark_all_paid')
                        ->label(__('custom.mark_all_paid'))
                        ->icon('heroicon-o-check-circle')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (Model $record) {
                            Order::markAllPaid($record);
                            Notification::make()
                                ->title(__('custom.all_paid_success'))
                                ->success()
                                ->send();
                        })
                        ->hidden(fn (Order $record) => $record->unpaid_count === 0 || $record->trashed())
                        ->after(fn ($livewire) => $livewire->resetTable()),
                    RestoreAction::make()->color('success'),
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Model $record): string => ViewOrder::getUrl([$record->id]))
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('custom.add_order'))
                    ->url(route('filament.admin.resources.orders.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
    }
}
