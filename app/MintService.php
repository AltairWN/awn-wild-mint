<?php

namespace App;

use Illuminate\Support\Carbon;

class MintService
{
    private string $dateStart = '2025-06-13';
    private string $dateEnd = '2025-06-16';

    public function getText(): string
    {
        $start = Carbon::createFromFormat('Y-m-d', $this->dateStart);
        $end = Carbon::createFromFormat('Y-m-d', $this->dateEnd);

        if($start && $end) {
            $start->startOfDay();
            $end->endOfDay();

            if ($start->isFuture()) {
                $days = (int) now()->startOfDay()->diffInDays($start);
                $daysString = match ($days) {
                    1 => 'день',
                    2, 3, 4 => 'дня',
                    default => 'дней'
                };
                return "🌿Дикая Мята☀️ наступит через $days $daysString!";
            }

            if($start->isCurrentDay()) {
                return '🌿Дикая Мята☀️ стартует сегодня!';
            }

            if($end->isCurrentDay() || $end->isFuture()) {
                return '🌿Дикая Мята☀️ происходит прямо сейчас!';
            }

            return '🌿Дикая Мята☀️ 2025 прошла!';
        }

        return "";
    }

    public function needSendNotification(): bool
    {
        $start = Carbon::createFromFormat('Y-m-d', $this->dateStart)?->startOfDay();

        return $start->isCurrentDay() || $start->isFuture();
    }
}
