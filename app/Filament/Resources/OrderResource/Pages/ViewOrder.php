<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Livewire\ListProducts22;
use App\Livewire\OrderListTableComponent;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                    return $record->name . ' (' . Carbon::parse($record->date)->format('d F Y') . ')';
                })
                ->schema([
                    Grid::make()
                        ->schema([
                            TextEntry::make('promo')
                                ->label('Discount (%)')
                                ->suffix('%')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('order_fee')
                                ->label('Order Fee')
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('delivery_fee')
                                ->label('Delivery fee')
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('tip')
                                ->label('Tip')
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                            TextEntry::make('total_fee')
                                ->label('Total Fee')
                                ->money('IDR. ', locale: 'id')
                                ->inlineLabel()
                                ->columnSpanFull(),
                        ])->columnSpan(1),

                    Grid::make()->schema([
                        TextEntry::make('discount_with_percentage')
                            ->label('Discount')
                            ->inlineLabel()
                            ->columnSpan(1),
                        TextEntry::make('additional_discount_with_percentage')
                            ->label('Additional Discount')
                            ->inlineLabel()
                            ->columnSpan(1),
                    ])->columnSpan(1)->columns(1)
                ])
                ->collapsible()
                ->columns(),
            Section::make('Order List')->schema([
                Livewire::make(OrderListTableComponent::class, ['id' => $this->record->id])
            ])
                ->collapsible()
        ]);
    }
}
