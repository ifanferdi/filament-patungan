<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(5)->schema([
                    Section::make('Promo, Fee, and Tip')->schema([
                        TextInput::make('promo')
                            ->label('Promo')
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
                            ->label('Order Fee')
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
                            ->label('Delivery fee')
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
                            ->label('Tip')
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
                            ->label('Total Fee')
                            ->required()
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
                            ->label('Discount')
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
                            ->label('Percentage')
                            ->default('0')
                            ->numeric()
                            ->suffix('%', true)
                            ->readOnly()
                            ->columnSpan(['xl' => 2, 'md' => 2, 'default' => 4]),
                        TextInput::make('additional_discount')
                            ->label('Additional Discount')
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
                            ->label('Percentage')
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
                            ->label('Total')
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp. ', true)
                            ->currencyMask('.', ','),
                        TextInput::make('total_with_promo')
                            ->label('Total With Discount (%)')
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
                                ->label('Name')
                                ->string()
                                ->required()
                                ->placeholder('Product Name')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('price')
                                ->label('Price')
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
                Tables\Columns\TextColumn::make('author.name')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn(Model $record): string => Pages\ViewOrder::getUrl([$record->id]));
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
