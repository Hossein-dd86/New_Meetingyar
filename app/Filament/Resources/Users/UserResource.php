<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Resources\Form;
use Illuminate\Support\Facades\Hash;
use Filament\Tables;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'کاربران';
    protected static ?string $recordTitleAttribute = 'کاربران';
    protected static ?string $title = 'کاربران';
    protected static ?string $breadcrumb ='کاربران';
    protected static ?string $modelLabel = 'کاربران';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            TextInput::make('name')
                ->label('نام')
                ->required(),

            TextInput::make('email')
                ->label('ایمیل')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('phone')
                ->label('موبایل')
                ->tel()
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->label('رمز عبور')
                ->password()
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->required(fn ($context) => $context === 'create')
                ->visible(fn ($context) => $context === 'create'),

            // 👇 این بخش دقیقا همونیه که پرسیدی
            Select::make('role')
                ->label('نقش کاربر')
                ->options([
                    'admin' => 'مدیر',
                    'barber' => 'آرایشگر',
                    'user' => 'کاربر عادی',
                ])
                ->reactive() // 👈 خیلی مهم
                ->required(),

            Toggle::make('is_active')
                ->label('فعال باشد')
                ->default(true),

            // Select::make('services')
            //     ->label('سرویس‌ها')
            //     ->relationship('services', 'title')
            //     ->multiple()
            //     ->searchable()
            //     ->preload()
            //     ->visible(fn ($get) => $get('role') === 'barber'),

            TextInput::make('work_time')
                ->label('ساعت کاری')
                ->placeholder('مثلاً 9 تا 17')
                ->visible(fn ($get) => $get('role') === 'barber'),

            TextInput::make('price')
                ->label('قیمت پیشنهادی (تومان)')
                ->numeric()
                ->prefix('تومان')
                ->visible(fn ($get) => $get('role') === 'barber'),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([


            TextColumn::make('name')
                ->label('نام')
                ->searchable(),

            TextColumn::make('phone')
                ->label('موبایل')
                ->searchable(),

            TextColumn::make('email')
                ->label('ایمیل')
                ->searchable(),

            TextColumn::make('created_at')
                ->label('ساخته شده')
                ->dateTime('Y-m-d'),

            TextColumn::make('role')
                ->label('نقش')
                ->getStateUsing(fn ($record) => match($record->role) {
                    "admin" => 'مدیر',
                    "user" => 'کاربر',
                    "barber" => 'آرایشگر',
                })
                ->searchable(),

        ])
        ->filters([
            SelectFilter::make('role')
                ->label('نوع کاربر')
                ->options([
                    'admin' => 'مدیر',
                    'barber' => 'آرایشگر',
                    'user' => 'کاربر عادی',
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
