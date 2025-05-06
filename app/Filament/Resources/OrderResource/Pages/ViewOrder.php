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
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function authorizeAccess(): void
    {
        if (Route::is('filament.admin.resources.orders.view')) {
            abort_unless($this->getRecord()->author_id === auth()->id()
                || auth()->user()->username === 'admin', 403);
        }
    }

    protected function getHeaderActions(): array
    {
        if (Auth::guest() || Auth::id() !== static::getRecord()->author_id) return [];

        return [
            Actions\Action::make('go_to_public_page')
                ->label(__('custom.public_page'))
                ->color(Color::Gray)
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (Model $record) => route('public.orders.show', $record->id),
                    shouldOpenInNewTab: true)
                ->hidden(Route::is('public.orders.show')),
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
            Actions\EditAction::make()->color(Color::Blue),
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
                    ])->columnSpan(1)->columns(1),
                    Grid::make()->schema([
                        TextEntry::make('preferred_payment')
                            ->label(__('custom.preferred_payment'))
                            ->inlineLabel()
                            ->copyable()
                            ->copyableState(fn (Model $record
                            ): string => $record->author->preferredPayment()->account_number)
                            ->copyMessage('Copied!')
                            ->weight('bold')
                            ->columnSpan(1)
                            ->state(function (Model $record): string {
                                $payment = $record->author->preferredPayment();
                                return "[" . Str::upper($payment->provider) . "] {$payment->account_number}";
                            }),
                        TextEntry::make('other_payment')
                            ->label(__('custom.other_payment'))
                            ->inlineLabel()
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->columns()
                            ->columnSpan(1)
                            ->expandableLimitedList()
                            ->state(function (Model $record) {
                                $payments = $record->author->otherPayment();
                                $data = $payments->map(function ($payment) {
                                    return "[" . Str::upper($payment->provider) . "] {$payment->account_number}";
                                });
                                return $data;
                            }),
                    ])->columnSpan(1)->columns(1),
                ])
                ->collapsible()
                ->columns(3),
            Section::make(__('custom.order_list'))
                ->schema([
                    Livewire::make(OrderListTableComponent::class, ['id' => $this->record->id])
                ])
                ->collapsible()
        ]);
    }
}
