<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;
use Tiptap\Nodes\Text;
use Morilog\Jalali\Jalalian;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationLabel = 'رزرواسیون';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'رزرواسیون';
    protected static ?string $breadcrumb ='رزرواسیون';
    protected static ?string $modelLabel = 'رزرواسیون';

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            Hidden::make('barber_id')
                ->default(fn () => auth()->id())
                ->reactive()
                ->required(),

            Select::make('service_id')
                ->label('سرویس')
                // نمایش نام سرویس‌ها، اما مقدار ذخیره شده همچنان ID خواهد بود
                ->options(fn () => Service::where('barber_id', auth()->id())->pluck('name', 'id'))
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    // $state همان ID انتخاب شده توسط کاربر است
                    $serviceId = $state;
                    $price = Service::find($serviceId)?->price; // جستجو بر اساس ID

                    if ($price !== null) {
                        $set('price', $price);
                    } else {
                        $set('price', 0); // اگر سرویس پیدا نشد یا قیمت نداشت
                    }
                })
                ->required(),

            TextInput::make('name')
                ->label('نام کامل')
                ->required(),

            TextInput::make('phone')
                ->label('موبایل')
                ->tel()
                ->required(),
                TextInput::make('email')
                ->label('ایمیل')
                ->email()
                ->required(),

            TextInput::make('password')
                ->label('رمز عبور')
                ->password()
                ->required()
                ->dehydrateStateUsing(fn ($state) => bcrypt($state)),





            DatePicker::make('date')
                ->label('تاریخ رزرو')
                ->minDate(now())
                ->reactive()
                ->formatStateUsing(function ($state) {
                    return Jalalian::fromDateTime($state)->format('Y/m/d');
                })
                ->required(),

            Radio::make('start_time')
                ->label('ساعت رزرو')
                ->reactive()

                ->options(function (callable $get) {
                    $date = $get('date');
                    $serviceId = $get('service_id');

                    if (!$date || !$serviceId) return [];

                    $service = Service::select('time')->find($serviceId);
                    $duration = $service->time; // دقیقه

                    $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
                    $workingHour = WorkingHour::where([
                        ['barber_id', 1],
                        ['day', $dayOfWeek]
                    ])
                        ->first();

                    if (!$workingHour) return [];

                    $start = Carbon::parse($workingHour->start_time);
                    $end = Carbon::parse($workingHour->end_time);

                    $slots = [];
                    $current = $start->copy();

                    while ($current->lte($end->copy()->subMinutes($duration))) {
                        $slotEnd = $current->copy()->addMinutes($duration);
                        $label = $current->format('H:i') . ' - ' . $slotEnd->format('H:i');
                        $value = $current->format('H:i'); // فقط زمان شروع ذخیره می‌شود
                        $slots[$label] = $value;
                        $current->addMinutes($duration);
                    }

                    return $slots;
                })
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
                ->default(function (callable $get) {
                    $currentServiceId = $get('service_id');
                    if ($currentServiceId) {
                        // جستجو برای قیمت هنگام لود اولیه فرم
                        return Service::find($currentServiceId)?->price ?? 0;
                    }
                    return 0; // مقدار اولیه اگر service_id در لود اولیه وجود نداشته باشد
                })
                ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state)) // موقع ذخیره حذف کاما
                ->required(),
            ]);

    }

    /**
     * قبل از ذخیره Booking، کاربر جدید بساز و user_id را اضافه کن
     */
    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        // ساخت کاربر جدید
        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'], // رمز از قبل bcrypt شده
        ]);

        // حذف نام، موبایل و پسورد از داده Booking (فقط user_id لازم است)
        unset($data['name'], $data['phone'], $data['password']);

        // اضافه کردن user_id به داده Booking
        $data['user_id'] = $user->id;

        return $data;
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('service.name')->label('سرویس')->searchable(),
                TextColumn::make('name')->label('نام'),

                TextColumn::make('phone')->label('موبایل')->searchable(),
                TextColumn::make('date')
                    ->label('تاریخ رزرو')
                    ->formatStateUsing(fn ($state) =>
                    $state
                        ? Jalalian::fromDateTime($state)->format('Y/m/d')
                        : '-'
                    )
                    ->sortable(),
                TextColumn::make('start_time')->label('ساعت'),


            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
