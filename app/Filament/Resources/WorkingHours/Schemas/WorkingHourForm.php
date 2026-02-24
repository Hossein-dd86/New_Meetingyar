<?php

namespace App\Filament\Resources\WorkingHours\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class WorkingHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('barber_id')
                    ->required()
                    ->numeric(),
                Select::make('day')
                    ->options([1 => '1', '2', '3', '4', '5', '6', '7'])
                    ->required(),
                TimePicker::make('start_time')
                    ->required(),
                TimePicker::make('end_time')
                    ->required(),
            ]);
    }
}
