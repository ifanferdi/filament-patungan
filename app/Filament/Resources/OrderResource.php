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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    Section::make('Discount (%), Fee, and Tip')->collapsible()->schema([
                        TextInput::make('discount_percentage')
                            ->label('Discount (%)')
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->extraInputAttributes(['min' => 0, 'max' => 100])
                            ->columnSpan(2)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $this->triggerTotalBill($get, $set);
                            }),
                        TextInput::make('order_fee')
                            ->label('Order Fee')
                            ->numeric()
                            ->placeholder('0')
                            ->required()
                            ->prefix('Rp. ')
                            ->autofocus()
                            ->minValue(0)
                            ->currencyMask('.', ',', 0)
                            ->columnSpan(4)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $this->triggerTotalFee($get, $set);
                            }),
                        TextInput::make('delivery_fee')
                            ->label('Delivery fee')
                            ->numeric()
                            ->placeholder('0')
                            ->default(null)
                            ->prefix('Rp. ')
                            ->currencyMask('.', ',', 0)
                            ->minValue(0)
                            ->columnSpan(3)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $this->triggerTotalFee($get, $set);
                            }),
                        TextInput::make('tip')
                            ->label('Tip')
                            ->numeric()
                            ->placeholder('0')
                            ->prefix('Rp. ')
                            ->currencyMask('.', ',', 0)
                            ->minValue(0)
                            ->columnSpan(3)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $this->triggerTotalFee($get, $set);
                            }),
                        TextInput::make('total_fee')
                            ->label('Total Fee')
                            ->numeric()
                            ->columnSpan(3)
                            ->placeholder('0')
                            ->prefix('Rp. ')
                            ->currencyMask('.', ',', 0)
                            ->columnSpan(4)
                            ->readOnly(),
                    ])->columns(6)->columnSpanFull(),

                    Section::make('Discount')->collapsible()->schema([
                        TextInput::make('discount')
                            ->label('Discount')
                            ->placeholder('0')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->columnSpan(4)
                            ->currencyMask('.', ',', 0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $bill_before_discount = intval($get('total_bill_real')) ?? 0;
                                $discount = $get('discount');
                                $set('discount_percent', $discount > 0 ? $bill_before_discount / $discount : 0);
                            }),
                        TextInput::make('discount_percent')
                            ->label('Percentage')
                            ->default('0')
                            ->numeric()
                            ->suffix('%')
                            ->readOnly()
                            ->columnSpan(2)
                            ->currencyMask('.', ','),
                        TextInput::make('additional_discount')
                            ->label('Additional Discount')
                            ->placeholder('0')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->columnSpan(4)
                            ->currencyMask('.', ',', 0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $bill_before_discount = intval($get('total_bill_real')) ?? 0;
                                $discount = $get('discount');
                                $set('additional_discount_percent', $discount > 0 ? $bill_before_discount / $discount : 0);
                            }),
                        TextInput::make('additional_discount_percent')
                            ->label('Percentage')
                            ->default('0')
                            ->numeric()
                            ->suffix('%')
                            ->readOnly()
                            ->columnSpan(2)
                            ->currencyMask('.', ','),
                    ])->columns(6)->columnSpanFull(),
                ])->columnSpan(['xl' => 2, 'md' => 5]),

                Grid::make(1)->schema([
                    Section::make('Bill')->collapsible()->schema([
                        Split::make([
                            TextInput::make('bill_real')
                                ->label('Total Bill')
                                ->required()
                                ->default(0)
                                ->numeric()
                                ->readOnly()
                                ->prefix('Rp. ')
                                ->currencyMask('.', ','),
                            TextInput::make('bill_by_discount_percentage')
                                ->label('Total Bill By Discount (%)')
                                ->required()
                                ->default(0)
                                ->numeric()
                                ->readOnly()
                                ->prefix('Rp. ')
                                ->currencyMask('.', ','),
                        ]),
                        TextInput::make('bill_final')
                            ->label('Sub Total')
                            ->required()
                            ->default(0)
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp. ')
                            ->currencyMask('.', ','),
                    ]),
                    Section::make('Order List')->collapsible()->schema([
                        TableRepeater::make('order_list')->hiddenLabel()
                            ->headers([
                                Header::make('name'),
                                Header::make('price')->width('250px'),
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
                                    ->prefix('Rp. ')
                                    ->currencyMask('.', ','),
                            ])->afterStateUpdated(function (Get $get, Set $set) {
                                $this->triggerTotalBill($get, $set);
                            })->live()
                    ]),
                ])->columnSpan(['xl' => 3, 'md' => 5]),

            ])->columns(5);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            ]);
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
    function triggerTotalBill(Get $get, Set $set): void
    {
        $discount_percentage = intval($get('discount_percentage'));
        $prices = [];
        $prices_with_discount = [];
        foreach ($get('order_list') as $order) {
            $prices[] = intval($order['price']);
            $prices_with_discount[] = intval($order['price']) * $discount_percentage;
        }
        $set('bill_real', array_sum($prices));
        $set('bill_by_discount_percentage', array_sum($prices_with_discount));
    }

    /**
     * @param Get $get
     * @param Set $set
     * @return void
     */
    function triggerTotalFee(Get $get, Set $set): void
    {
        $order_fee = intval($get('order_fee'));
        $delivery_fee = intval($get('delivery_fee'));
        $tip = intval($get('tip'));
        $set('total_fee', array_sum([$order_fee, $delivery_fee, $tip]));
    }
}
