<?php

namespace App\Livewire;

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
use Livewire\Component;

class OrderListTableComponent extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public int $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderDetail::where('order_id', $this->id))
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Product Name'),
                TextColumn::make('price')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Price')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('discount_by_percentage')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Discount (%)')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('discount')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Discount')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('additional_discount')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Additional Discount')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('price_after_discount')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Price - All Discounts')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('fee')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Fee')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('final_price')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->label('Final Price')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                CheckboxColumn::make('is_paid')
                    ->label(__('custom.is_paid'))
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title(__('custom.paid_success'))
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated(false);
    }

    //    Discount
    //price-all discount
    //fee
    //final price
    //preseentase discount

    public function errorBagExcept($field)
    {
    }

    public function render()
    {
        return view('livewire.order-list-table-component');
    }
}
