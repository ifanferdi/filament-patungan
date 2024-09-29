<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return parent::mutateFormDataBeforeSave([...$data, 'order_list' => $this->data['order_list']]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $order_fee = (int)$data['order_fee'] ?? 0;
        $delivery_fee = (int)$data['delivery_fee'] ?? 0;
        $tip = (int)$data['tip'] ?? 0;
        $discount = (int)$data['discount'] ?? 0;
        $additional_discount = (int)$data['additional_discount'] ?? 0;
        $total = (int)$data['total'] ?? 0;
        $total_with_promo = (int)$data['total_with_promo'] ?? 0;

        $data = [
            ...$data,
            'promo' => (int)$data['promo'] ?? 100,
            'total_fee' => (int)array_sum([$order_fee, $delivery_fee, $tip]),
            'tip' => $tip,
            'discount' => $discount,
            'discount_percent' => (int)(ceil($discount / $total_with_promo * 100)),
            'additional_discount' => $additional_discount,
            'additional_discount_percent' => (int)ceil($additional_discount / $total * 100),
            'total' => $total,
            'total_with_promo' => $total_with_promo,
            'total_items' => count($data['order_list']),
        ];

        return parent::handleRecordUpdate($record, $data);
    }
}
