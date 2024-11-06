<?php

namespace App\Filament\Pages\Order;

use App\Filament\Resources\OrderResource\Pages\ViewOrder;

class PublicViewOrder extends ViewOrder
{
    protected static string $layout = 'filament-panels::components.layout.index-public';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static string $view = 'filament.pages.order.public-view-order';

    protected static bool $shouldRegisterNavigation = false;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
