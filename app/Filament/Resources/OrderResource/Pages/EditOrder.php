<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        if (Auth::check())
            abort_unless(static::getRecord()->author_id === auth()->id()
                || auth()->user()->username === 'admin', 403);
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return parent::mutateFormDataBeforeSave([...$data, 'order_list' => $this->data['order_list']]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        return parent::handleRecordUpdate($record, $this->getModel()::processOrder($data));
    }
}
