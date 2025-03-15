<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderDetail;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderListTableComponent extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public int $id;

    public function mount($id): void
    {
        $this->id = $id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->query(OrderDetail::where('order_id', $this->id))
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                CheckboxColumn::make('is_paid')
                    ->label(__('custom.is_paid'))
                    ->disabled(Auth::guest())
                    ->afterStateUpdated(function ($record, $state) {
                        $order = Order::select(['id'])
                            ->whereId($record->order_id)
                            ->withCount(['details_unpaid', 'details'])
                            ->first();
                        $order->update([
                            'paid_count' => $order->details_count - $order->details_unpaid_count,
                            'unpaid_count' => $order->details_unpaid_count
                        ]);
                        $order->save();

                        Notification::make()
                            ->title(__('custom.paid_success'))
                            ->success()
                            ->send();
                    }),
                TextColumn::make('name')
                    ->label('Product Name'),
                TextColumn::make('final_price')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->weight('bold')
                    ->color(fn ($record) => $record->is_paid ? '' : config('filament.colors.primary'))
                    ->label('Final Price')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('price')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Price')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('discount_by_percentage')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Discount (%)')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('discount')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Discount')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('additional_discount')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Additional Discount')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('price_after_discount')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Price - All Discounts')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('fee')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Fee')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
            ])
            ->paginated(false);
    }

    public function errorBagExcept($field)
    {
    }

    public function render()
    {
        return view('livewire.order-list-table-component');
    }
}
