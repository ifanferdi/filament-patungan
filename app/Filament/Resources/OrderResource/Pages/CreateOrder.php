<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected static string $model = Order::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return parent::mutateFormDataBeforeCreate([...$data, 'order_list' => $this->data['order_list']]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation($this->getModel()::processOrder($data));
    }
}
