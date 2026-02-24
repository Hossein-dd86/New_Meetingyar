<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;
use Filament\Forms\Components\TextInput\Mask;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'خدمات';
    protected static ?string $recordTitleAttribute = 'خدمات';
    protected static ?string $title = 'خدمات';
    protected static ?string $breadcrumb ='خدمات';
    protected static ?string $modelLabel = 'خدمات';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(components: [
            Hidden::make('barber_id')
            ->default(fn () => Filament::auth()->id())
            ->dehydrated()
            ->required(),
            TextInput::make('name')
                ->label('عنوان خدمات')
                ->required(),

            TextInput::make('price')
                ->label('قیمت')
                ->suffix('تومان')
                ->live() // مهم 👈 باعث میشه لحظه‌ای آپدیت بشه
                ->formatStateUsing(fn ($state) => $state ? number_format((float) $state) : 0)
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!$state) return;

                    // حذف کاماهای قبلی
                    $numeric = str_replace(',', '', $state);

                    if (is_numeric($numeric)) {
                        $set('price', number_format($numeric));
                    }
                })
                ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state)) // موقع ذخیره حذف کاما
                ->required(),
            TextInput::make('time')
             ->label('زمان(دقیقه)')
             ->required(),

            Textarea::make('description')
                ->label('توضیحات')
                ->rows(4),

            Toggle::make('is_active')
                ->label('فعال باشد')
                ->default(true),

        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->label('عنوان')->searchable(),

                TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn ($state) =>
                    $state
                        ? number_format($state) . ' تومان'
                        : '0 تومان'
                    )
                    ->sortable()
                    ->searchable(),
                    TextColumn::make('time')
             ->label('(دقیقه)زمان')
             ->searchable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn ($state) =>
                    $state
                        ? Jalalian::fromDateTime($state)->format('Y/m/d')
                        : '-'
                    )
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean()
                    ->searchable(),
            ])
        ->filters([
        SelectFilter::make('is_active')
            ->label('وضعیت')
            ->options([
                '1' => 'فعال',
                '0' => 'غیر فعال',
            ]),
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {

        // فقط سرویس‌های آرایشگر لاگین‌شده
        return parent::getEloquentQuery()
            ->where('barber_id', Filament::auth()->id());
    }
}
