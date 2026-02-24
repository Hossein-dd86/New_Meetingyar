<?php

namespace App\Filament\Resources\SmsSenders\Schemas;

use Filament\Schemas\Schema;

class SmsSenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
