<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use \Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Enums\CalendarViewType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\Filament\Actions\ViewAction;

class MyCalendarWidget extends CalendarWidget
{
    public ?array $selectedBooking = null;
    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;
    protected $listeners = ['openBookingModal'];
    protected ?string $defaultEventClickAction = 'view'; // view and edit actions are provided by us, but you can choose any action you want, even your own custom ones
    protected bool $eventClickEnabled = true;

    public function viewBookingAction(): ViewAction
    {
        return ViewAction::make('viewBooking')
            ->model(Booking::class)
            ->recordKey(fn ($record) => $record->getKey());
    }

    protected function getEvents(FetchInfo $info): Collection | array
    {
        $start = $info->start; // شروع هفته
        $end = $info->end;     // پایان هفته

        // فقط رزرو‌هایی که در هفته جاری هستند
        return Booking::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(function (Booking $booking) {
                // ترکیب تاریخ و زمان شروع
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $booking->date . ' ' . $booking->start_time,'Asia/Tehran');
                $endDateTime = $startDateTime->copy()->addHour();
                // پایان = یک ساعت بعد
//                $endDateTime = $startDateTime->copy()->addHour();

                return CalendarEvent::make()
                    ->key($booking->id) // ضروری
                    ->title($booking->name)
                    ->url("https://bsc.ir")

                    ->start($startDateTime)
                    ->end($endDateTime);
            });
    }
    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        // Validate the data and handle the event click
        // $event contains the clicked event record
        // you can also access it via $info->record
        dd($info, $event, $action);
    }
    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction(),
            $this->editAction(),
            $this->deleteAction(),
        ];
    }
    public function openBookingModal($data)
    {
        $booking = Booking::find($data['id']);

        if (! $booking) {
            return;
        }

        $this->selectedBooking = [
            'name' => $booking->name,
            'date' => $booking->date,
            'start_time' => $booking->start_time,
            'description' => $booking->description,
        ];

        $this->mountAction('viewBooking');
    }
    public function getHeaderActions(): array
    {
        return [
            Action::make('viewBooking')
                ->label('مشاهده رزرو')
                ->modalHeading('جزئیات رزرو')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('بستن')
                ->form([
                    TextInput::make('name')
                        ->label('نام')
                        ->disabled(),

                    TextInput::make('date')
                        ->label('تاریخ')
                        ->disabled(),

                    TextInput::make('start_time')
                        ->label('ساعت شروع')
                        ->disabled(),

                    Textarea::make('description')
                        ->label('توضیحات')
                        ->disabled(),
                ])
                ->fillForm(fn () => $this->selectedBooking ?? []),
        ];
    }
    public function getOptions(): array
    {
        return [
            'eventClick' => "
            function(info) {
                alert(info.event.id);
            }
        ",
        ];
    }
}
