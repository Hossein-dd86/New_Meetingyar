<?php

namespace App\Filament\Resources\SmsSenders\Pages;

use App\Filament\Resources\SmsSenders\SmsSenderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSmsSender extends EditRecord
{
    protected static string $resource = SmsSenderResource::class;
    protected static ?string $title = 'ارسال پیامک';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
