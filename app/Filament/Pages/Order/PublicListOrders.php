<?php

namespace App\Filament\Pages\Order;

use Filament\Pages\Page;
use App\Filament\Resources\OrderResource\Pages\ListOrders;

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
}
