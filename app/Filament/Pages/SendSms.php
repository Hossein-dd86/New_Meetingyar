<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;

class SendSms extends Page
{
    protected static ?string $navigationLabel = 'ارسال پیامک';
    // متغیرهای فرم
    public $phone;
    public $message;
    public $selectedContacts = [];

    // فرم Livewire

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('phone')
                ->label('شماره موبایل (در صورت انتخاب از جدول خالی بگذارید)')
                ->tel(),

            Textarea::make('message')
                ->label('متن پیامک')
                ->required(),

            Tables\Components\Table::make('contacts')
                ->label('مخاطبین شما')
                ->columns([
                    TextColumn::make('name')->label('نام'),
                    TextColumn::make('phone')->label('موبایل'),
                ])
                ->checkboxColumn() // برای انتخاب چندتایی
                ->records(function () {
                    return Contact::where('user_id', auth()->id())->get();
                }),
        ];
    }

    // تابع ارسال پیامک
    public function sendSms()
    {
        $numbers = [];

        if (!empty($this->phone)) {
            $numbers[] = $this->phone;
        }

        foreach ($this->selectedContacts as $contact) {
            $numbers[] = $contact['phone']; // توجه: checkbox داده‌ها رو به صورت آرایه میده
        }

        if (empty($numbers)) {
            $this->notify('warning', 'هیچ شماره‌ای برای ارسال پیامک انتخاب نشده است.');
            return;
        }

        foreach ($numbers as $number) {
            $url = "https://media.sms24.ir/SMSInOutBox/SendSms?username=".config('sms.username').
                "&password=".config('sms.password').
                "&from=".config('sms.from').
                "&to={$number}&text=" . urlencode($this->message);

            Http::get($url);
        }

        $this->notify('success', 'پیامک‌ها با موفقیت ارسال شد.');
    }
}
