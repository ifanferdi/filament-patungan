<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Livewire\OrderListTableComponent;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_all_paid')
                ->label(__('custom.mark_all_paid'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Model $record) {
                    Order::markAllPaid($record);
                    Notification::make()
                        ->title(__('custom.all_paid_success'))
                        ->success()
                        ->send();
                })
                ->hidden(fn (Order $record) => $record->details_unpaid()->count() === 0),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Order Data')
                ->description(function ($record): string {
                    return $record->name . ' (' . Carbon::parse($record->date)->format('d F Y') . ') - ' . $record->author->name;
                })
                ->schema([
                    Grid::make()
                        ->schema([
                            TextEntry::make('promo')
                                ->label(__('custom.discount') . '%')
                                ->suffix('%')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('order_fee')
                                ->label(__('custom.order_fee'))
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('delivery_fee')
                                ->label(__('custom.delivery_fee'))
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('tip')
                                ->label(__('custom.tip'))
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('total_fee')
                                ->label(__('custom.total_fee'))
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                        ])->columnSpan(1),
                    Grid::make()->schema([
                        TextEntry::make('discount_with_percentage')
                            ->label(__('custom.discount'))
                            ->inlineLabel()
                            ->columnSpan(1),
                        TextEntry::make('additional_discount_with_percentage')
                            ->label(__('custom.additional_discount'))
                            ->inlineLabel()
                            ->columnSpan(1),
                    ])->columnSpan(1)->columns(1)
                ])
                ->collapsible()
                ->columns(),
            Section::make(__('custom.order_list'))
                ->schema([
                    Livewire::make(OrderListTableComponent::class, ['id' => $this->record->id])
                ])
                ->collapsible()
        ]);
    }
}
