<?php

namespace App\Filament\Resources\SmsSenders\Pages;

use App\Filament\Resources\SmsSenders\SmsSenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSmsSenders extends ListRecords
{
    protected static string $resource = SmsSenderResource::class;
    protected static ?string $title = 'ارسال پیامک';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
