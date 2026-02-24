<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;
    protected static ?string $title = 'رزرواسیون';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
