<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label(__('filament-panels::pages/auth/register.form.name.label'))
                    ->options(User::orderBy('name')->get()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->default(auth()->user()->id)
                    ->disableOptionWhen(fn (): bool => auth()->user()->username !== 'admin'),
                Split::make([
                    Select::make('provider')
                        ->label(__('custom.provider'))
                        ->options(config('payment.providers'))
                        ->searchable()
                        ->required(),
                    TextInput::make('account_number')
                        ->label(__('custom.account_number'))
                        ->placeholder(__('custom.account_number'))
                        ->required()
                        ->numeric()
                ])->columnSpanFull(),
                Checkbox::make('is_primary')
                    ->label(__('custom.is_primary') . '?')
                    ->default(false)
                // TODO: BUAT LOGIC HANYA 1 PRIMARY PAYMENT PER USER
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            //            'create' => Pages\CreatePayment::route('/create'),
            //            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
