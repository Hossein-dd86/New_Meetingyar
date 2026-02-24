<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PanelCalendar extends Widget
{
    protected static ?string $heading = 'Calendar';

    protected string $view = 'filament.widgets.panel-calendar';

    // این باعث میشه ویجت کل عرض داشبورد رو اشغال کنه
    protected int|string|array $columnSpan = 'full';

    // میتونی داده‌های ماه و روزها رو از اینجا به Blade پاس بدی
    public function getData(): array
    {
        return [
            'month' => 'November 2017',
            'daysOfWeek' => ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
            'days' => range(29, 30), // نمونه، میتونی دیتابیس یا آرایه واقعی رو اینجا بذاری
            'events' => [
                3 => [['title' => 'Test Event 1', 'class' => 'bg-info']],
                8 => [
                    ['title' => 'Test Event 2', 'class' => 'bg-success'],
                    ['title' => 'Test Event 3', 'class' => 'bg-danger']
                ],
                20 => [['title' => 'Test Event with Super Duper Really Long Title', 'class' => 'bg-primary']],
            ],
        ];
    }
}
