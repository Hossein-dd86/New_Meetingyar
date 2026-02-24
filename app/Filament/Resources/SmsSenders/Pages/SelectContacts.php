<?php

namespace App\Filament\Resources\SmsSenders\Pages;

use App\Filament\Resources\SmsSenders\SmsSenderResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Contact;
use Filament\Tables\Actions; // این مهم‌ترین بخش است
use Filament\Actions\Action;


class SelectContacts extends Page implements HasTable
{
    use InteractsWithTable;
    protected bool $isTableSelectionEnabled = true;

    protected static string $resource = SmsSenderResource::class;
    protected static ?string $title = 'ارسال پیامک';

    protected string $view = 'filament.resources.sms-sender-resource.pages.select-contacts';
    public function table(Table $table): Table
    {

    return $table
            ->query(
                Contact::query()
                    ->where('user_id', auth()->id())
            )
            ->columns([
                TextColumn::make('name')->label('نام')->searchable(),
                TextColumn::make('phone')->label('موبایل'),


            ]);


    }
    protected function getTableQuery()
    {
        return \App\Models\Contact::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label('نام'),
            Tables\Columns\TextColumn::make('phone')->label('شماره'),
        ];
    }

    // فعال کردن انتخاب رکوردها
    protected function isTableSelectionEnabled(): bool
    {
        return true;
    }

    protected function getTableBulkActions(): array
    {
        return [
           Action::make('send')
                ->label('ارسال پیام به انتخاب شده‌ها')
                ->action(function ($records) {
                    // رکوردهای انتخاب شده اینجا هستند
                    dd($records);
                }),
        ];
    }
}
