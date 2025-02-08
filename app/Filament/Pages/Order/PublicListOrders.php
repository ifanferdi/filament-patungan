<?php

namespace App\Filament\Pages\Order;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use Illuminate\Database\Eloquent\Builder;

class PublicListOrders extends ListOrders
{
    protected static string $layout = 'filament-panels::components.layout.index-public';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.order.public-list-orders';

    protected static bool $shouldRegisterNavigation = false;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): ?Builder
    {
        return static::getModel()::query();
    }
}
