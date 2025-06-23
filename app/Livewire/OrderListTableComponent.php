<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderDetail;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderListTableComponent extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Model $record;

    public function mount($record): void
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->query(OrderDetail::with(['person', 'order'])->where('order_id', $this->record->id))
            ->columns([
                TextColumn::make('#')
                    ->width('50px')
                    ->alignCenter()
                    ->rowIndex(),
                CheckboxColumn::make('is_paid')
                    ->label(__('custom.is_paid'))
                    ->width('50px')
                    ->alignCenter()
                    ->disabled(fn ($record) => Auth::id() !== $record->order->author_id)
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
                TextColumn::make('person')
                    ->label('Person')
                    // ->counts('person')
                    ->visible(fn () => $this->record->details_person_count > 0)
                    ->formatStateUsing(fn (Model $record): string => $record->person->name_with_phone_number),
                TextColumn::make('final_price')
                    ->width('100px')
                    ->money('IDR. ', locale: 'id')
                    ->weight('bold')
                    ->color(fn ($record) => $record->is_paid ? '' : 'primary')
                    ->label('Final Price')
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('price')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Price')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('discount_by_percentage')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Discount (%)')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('discount')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Discount')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('additional_discount')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Additional Discount')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('price_after_discount')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Price - All Discounts')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
                TextColumn::make('fee')
                    ->color('gray')
                    ->money('IDR. ', locale: 'id')
                    ->label('Fee')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([Sum::make()->label('')->money('IDR', locale: 'id')]),
            ])
            ->toggleColumnsTriggerAction(fn (Action $action) => $action->button()->label('Columns'))
            ->paginated(false);
    }

    public function render()
    {
        return view('livewire.order-list-table-component');
    }
}
