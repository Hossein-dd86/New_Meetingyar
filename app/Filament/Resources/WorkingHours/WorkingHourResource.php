<?php

namespace App\Filament\Resources\WorkingHours;

use App\Filament\Resources\WorkingHours\Pages\CreateWorkingHour;
use App\Filament\Resources\WorkingHours\Pages\EditWorkingHour;
use App\Filament\Resources\WorkingHours\Pages\ListWorkingHours;
use App\Filament\Resources\WorkingHours\Schemas\WorkingHourForm;
use App\Filament\Resources\WorkingHours\Tables\WorkingHoursTable;
use App\Models\WorkingHour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;

class WorkingHourResource extends Resource
{
    protected static ?string $model = WorkingHour::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'ساعات کاری';
    protected static ?string $recordTitleAttribute = 'ساعات کاری';
    protected static ?string $title = 'ساعات کاری';
    protected static ?string $breadcrumb ='ساعات کاری';
    protected static ?string $modelLabel = 'ساعات کاری';

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            Hidden::make('barber_id')
             ->default(fn () => Auth::id()) // خودش لاگین شده
                ->required(),

            Select::make('day')
                ->label('روز هفته')
                ->options([
                    '1' => 'شنبه',
                    '2' => 'یکشنبه',
                    '3' => 'دوشنبه',
                    '4' => 'سه‌شنبه',
                    '5' => 'چهارشنبه',
                    '6' => 'پنج‌شنبه',
                    '7' => 'جمعه',
                ])
                ->required(),

            TimePicker::make('start_time')
                ->label('ساعت شروع')
                ->required(),

            TimePicker::make('end_time')
                ->label('ساعت پایان')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('day')
             ->label('روز هفته')
              ->getStateUsing(fn ($record) => match($record->day) {
                     "1" => 'شنبه',
                 "2" => 'یکشنبه',
                 "3" => 'دوشنبه',
                 "4" => 'سه‌شنبه',
                 "5" => 'چهارشنبه',
                 "6" => 'پنج‌شنبه',
                "7" => 'جمعه',
            }),
            TextColumn::make('start_time')->label('ساعت شروع'),
            TextColumn::make('end_time')->label('ساعت پایان'),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkingHours::route('/'),
            'create' => CreateWorkingHour::route('/create'),
            'edit' => EditWorkingHour::route('/{record}/edit'),
        ];
    }
}
