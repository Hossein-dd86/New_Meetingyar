<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;
    protected static ?string $title = 'مخاطبین';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
