<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    TextInput::make('name')
                        ->label(__('custom.order_name'))
                        ->required()
                        ->placeholder(__('custom.example') . ': Roscik')
                        ->maxLength(255)
                        ->columnSpan(1),
                    DatePicker::make('date')
                        ->native(false)
                        ->label(__('custom.order_date'))
                        ->displayFormat('d mm Y')
                        ->default(now())
                        ->required()
                        ->columnSpan(1)
                ])->from('md'),
                Grid::make(5)->schema([
                    Section::make('Promo, Fee, and Tip')->schema([
                        TextInput::make('promo')
                            ->label(__('custom.promo'))
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->suffix('%', true)
                            ->minValue(0)
                            ->maxValue(100)
                            ->extraInputAttributes(['min' => 0, 'max' => 100])
                            ->columnSpan(['xl' => 2, 'md' => 3, 'default' => 6])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $class = new OrderResource();
                                $class->triggerTotalBill($get, $set);
                                $class->triggerDiscountPercentage($get, $set);
                            }),
                        TextInput::make('order_fee')
                            ->label(__('custom.order_fee'))
                            ->numeric()
                            ->placeholder('0')
                            ->prefix('Rp. ', true)
                            ->autofocus()
                            ->minValue(0)
                            ->currencyMask('.', ',', 0)
                            ->columnSpan(['md' => 2, 'default' => 6])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $class = new OrderResource();
                                $class->triggerTotalFee($get, $set);
                            }),
                        TextInput::make('delivery_fee')
                            ->label(__('custom.delivery_fee'))
                            ->numeric()
                            ->placeholder('0')
                            ->default(null)
                            ->prefix('Rp. ', true)
                            ->currencyMask('.', ',', 0)
                            ->minValue(0)
                            ->columnSpan(['md' => 2, 'default' => 6])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $class = new OrderResource();
                                $class->triggerTotalFee($get, $set);
                            }),
                        TextInput::make('tip')
                            ->label(__('custom.tip'))
                            ->numeric()
                            ->placeholder('0')
                            ->prefix('Rp. ', true)
                            ->currencyMask('.', ',', 0)
                            ->minValue(0)
                            ->columnSpan(['md' => 2, 'default' => 6])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $class = new OrderResource();
                                $class->triggerTotalFee($get, $set);
                            }),
                        TextInput::make('total_fee')
                            ->label(__('custom.total_fee'))
                            ->numeric()
                            ->placeholder('0')
                            ->prefix('Rp. ', true)
                            ->currencyMask('.', ',', 0)
                            ->columnSpan(['xl' => 4, 'md' => 3, 'default' => 6])
                            ->readOnly(),

                    ])
                        ->collapsible()
                        ->columns(6)
                        ->columnSpan(['xl' => 3, 'md' => 5]),

                    Section::make('Discount')->schema([
                        TextInput::make('discount')
                            ->label(__('custom.discount'))
                            ->placeholder('0')
                            ->numeric()
                            ->prefix('Rp. ', true)
                            ->columnSpan(4)
                            ->currencyMask('.', ',', 0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $class = new OrderResource();
                                $class->triggerDiscountPercentage($get, $set);
                            }),
                        TextInput::make('discount_percent')
                            ->label(__('custom.percentage'))
                            ->default('0')
                            ->numeric()
                            ->suffix('%', true)
                            ->readOnly()
                            ->columnSpan(['xl' => 2, 'md' => 2, 'default' => 4]),
                        TextInput::make('additional_discount')
                            ->label(__('custom.additional_discount'))
                            ->placeholder('0')
                            ->numeric()
                            ->prefix('Rp. ', true)
                            ->columnSpan(4)
                            ->currencyMask('.', ',', 0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $class = new OrderResource();
                                $class->triggerAdditionalDiscountPercentage($get, $set);
                            }),
                        TextInput::make('additional_discount_percent')
                            ->label(__('custom.percentage'))
                            ->default('0')
                            ->numeric()
                            ->suffix('%', true)
                            ->readOnly()
                            ->columnSpan(['xl' => 2, 'md' => 2, 'default' => 4]),
                    ])
                        ->collapsible()
                        ->columns(6)
                        ->columnSpan(['xl' => 2, 'md' => 5]),
                ]),

                Section::make('Order List')->schema([
                    Split::make([
                        TextInput::make('total')
                            ->label(__('custom.total'))
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp. ', true)
                            ->currencyMask('.', ','),
                        TextInput::make('total_with_promo')
                            ->label(__('custom.total_with_discount'))
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp. ', true)
                            ->currencyMask('.', ','),
                    ]),

                    TableRepeater::make('order_list')
                        ->hiddenLabel()
                        ->relationship('details')
                        ->live()
                        ->headers([
                            Header::make('name')->markAsRequired(),
                            Header::make('price')->markAsRequired(),
                        ])
                        ->schema([
                            TextInput::make('name')
                                ->label(__('custom.name'))
                                ->string()
                                ->required()
                                ->placeholder(__('custom.product_name'))
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('price')
                                ->label(__('custom.price'))
                                ->numeric()
                                ->required()
                                ->placeholder(0)
                                ->columnSpan(1)
                                ->prefix('Rp. ', true)
                                ->currencyMask('.', ','),
                        ])->afterStateUpdated(function (Get $get, Set $set) {
                            $class = new OrderResource();
                            $class->triggerTotalBill($get, $set);
                            $class->triggerAdditionalDiscountPercentage($get, $set);
                            $class->triggerDiscountPercentage($get, $set);
                        })
                ])
                    ->collapsible()

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('custom.order_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('custom.date'))
                    ->date('d F Y'),
                Tables\Columns\TextColumn::make('author.name')
                    ->label(__('custom.author'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('details_count')
                    ->label(__('custom.total_products'))
                    ->counts('details')
                    ->badge()
                    ->suffix(' ' . Str::lower(__('custom.item'))),
                Tables\Columns\TextColumn::make('details_sum_final_price')
                    ->label(__('custom.final_price'))
                    ->badge()
                    ->sum('details', 'final_price')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('custom.trashed'))
                    ->badge()
                    ->color('danger')
                    ->icon('heroicon-m-trash')
                    ->formatStateUsing(fn (string $state): string => __('custom.trashed'))
                    ->hidden(function ($livewire) {
                        return !isset($livewire->getTableFilterState('trashed')['value']) || $livewire->getTableFilterState('trashed')['value'] === '';
                    }),
                Tables\Columns\TextColumn::make('unpaid_count')
                    ->label(__('custom.is_paid'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state > 0 ? $state . ' ' . __('custom.unpaid') : __('custom.all_paid'))
                    ->color(fn (string $state): string => $state > 0 ? 'danger' : 'success')
                    ->icon(fn (string $state): string => $state > 0 ? '' : 'heroicon-o-check-circle')
                ,])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\RestoreAction::make()->color('success'),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\Action::make('mark_all_paid')
                        ->label(__('custom.mark_all_paid'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Model $record) {
                            $record->details()->update(['is_paid' => true]);
                            $record->save();
                            Notification::make()
                                ->title(__('custom.all_paid_success'))
                                ->success()
                                ->send();

                        })
                        ->hidden(fn (Order $record) => $record->unpaid_count === 0)
                        ->after(fn ($livewire) => $livewire->resetTable()),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Model $record): string => Pages\ViewOrder::getUrl([$record->id]));
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * @param Get $get
     * @param Set $set
     * @return void
     */
    private function triggerTotalBill(Get $get, Set $set): void
    {
        $promo = (int)$get('promo');
        $prices = [];
        $prices_with_discount = [];
        foreach ($get('order_list') as $order) {
            $prices[] = (int)$order['price'];
            $prices_with_discount[] = (int)$order['price'] * $promo / 100;
        }
        $set('total', ceil(array_sum($prices)));
        $set('total_with_promo', ceil(array_sum($prices_with_discount)));
    }

    /**
     * @param Get $get
     * @param Set $set
     * @return void
     */
    private function triggerTotalFee(Get $get, Set $set): void
    {
        $order_fee = (int)$get('order_fee');
        $delivery_fee = (int)$get('delivery_fee');
        $tip = (int)$get('tip');
        $set('total_fee', array_sum([$order_fee, $delivery_fee, $tip]));
    }

    /**
     * @param Get $get
     * @param Set $set
     * @return void
     */
    private function triggerAdditionalDiscountPercentage(Get $get, Set $set): void
    {
        $bill_before_discount = (int)($get('total') ?? 0);
        $additional_discount = $get('additional_discount');
        if ($additional_discount > 0 && $bill_before_discount)
            $set(
                'additional_discount_percent',
                ceil($additional_discount / $bill_before_discount * 100)
            );
    }

    /**
     * @param Get $get
     * @param Set $set
     * @return void
     */
    private function triggerDiscountPercentage(Get $get, Set $set): void
    {
        $total_with_promo = (int)($get('total_with_promo') ?? 0);
        $discount = (int)($get('discount') ?? 0);
        if ($discount > 0 && $total_with_promo > 0) {
            $set(
                'discount_percent',
                ceil($discount / $total_with_promo * 100)
            );
        }
    }
}
