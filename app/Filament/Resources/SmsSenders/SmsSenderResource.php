<?php

namespace App\Filament\Resources\SmsSenders;

use App\Filament\Resources\SmsSenders\Pages\CreateSmsSender;
use App\Filament\Resources\SmsSenders\Pages\EditSmsSender;
use App\Filament\Resources\SmsSenders\Pages\ListSmsSenders;
use App\Filament\Resources\SmsSenders\Schemas\SmsSenderForm;
use App\Filament\Resources\SmsSenders\Tables\SmsSendersTable;
use App\Models\SmsSender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use App\Models\SmsCounter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use App\Models\Contact;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Filament\Forms\Components\Repeater;
use Morilog\Jalali\Jalalian;

class SmsSenderResource extends Resource
{
    protected static ?string $model = SmsCounter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'ارسال پیامک';
    protected static ?string $recordTitleAttribute = 'ارسال پیامک';
    protected static ?string $title = 'ارسال پیامک';
    protected static ?string $breadcrumb ='ارسال پیامک';
    protected static ?string $modelLabel = 'ارسال پیامک';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('phone')
                    ->label('شماره موبایل (در صورت انتخاب از جدول خالی بگذارید)')
                    ->tel(),

                Textarea::make('message')
                    ->label('متن پیامک')
                    ->required(),

//                Table::make('contacts')
//                    ->label('مخاطبین شما')
//                    ->columns([
//                        TextColumn::make('name')->label('نام'),
//                        TextColumn::make('phone')->label('موبایل'),
//                    ])
//                    ->filters([])
//                    ->bulkActions([])
//                    ->checkboxColumn() // این خط اضافه کن تا تیک بزنه
//                    ->records(function () {
//                        // فقط مخاطبین خود کاربر
//                        return Contact::where('user_id', auth()->id())->get();
//                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('کاربر'),

                Tables\Columns\TextColumn::make('count')
                    ->label('تعداد پیام ارسال شده'),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn ($state) =>
                    $state
                        ? Jalalian::fromDateTime($state)->format('Y/m/d')
                        : '-'
                    )
                    ->sortable(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        try {

            // ارسال پیامک از طریق تنظیمات قبلی
            $response = Http::post(config('sms.url'), [
                'username' => config('sms.username'),
                'password' => config('sms.password'),
                'to' => $data['phone'],
                'message' => $data['message'],
            ]);

            // افزایش شمارنده کاربر لاگین شده
            $counter = SmsCounter::firstOrCreate(
                ['user_id' => auth()->id()],
                ['count' => 0]
            );

            $counter->increment('count');

            Notification::make()
                ->title('پیامک با موفقیت ارسال شد')
                ->success()
                ->send();

        } catch (\Exception $e) {

            Notification::make()
                ->title('خطا در ارسال پیامک')
                ->danger()
                ->send();
        }

        // چون نمیخوای پیام ذخیره بشه
        // برمیگردونیم فقط user_id برای جلوگیری از خطای create
        return [
            'user_id' => auth()->id(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsSenders::route('/'),
            'create' => Pages\CreateSmsSender::route('/create'),
            'select-contacts' => Pages\SelectContacts::route('/select-contacts'),
        ];
    }


}
