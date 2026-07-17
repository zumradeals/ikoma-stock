<?php

namespace App\Support;

use Carbon\Carbon;

class HumanDate
{
    /**
     * Formate une date en texte lisible en français.
     *
     * - Aujourd'hui → "Aujourd'hui à 16h08"
     * - Hier        → "Hier à 16h08"
     * - < 7 jours   → "Lundi à 16h08"
     * - Sinon       → "12 juil. à 16h08"
     */
    public static function format(Carbon $date): string
    {
        $now   = Carbon::now();
        $time  = $date->format('H\hi'); // ex. 16h08

        if ($date->isToday()) {
            return "Aujourd'hui à {$time}";
        }

        if ($date->isYesterday()) {
            return "Hier à {$time}";
        }

        if ($date->greaterThan($now->copy()->subDays(7))) {
            $days = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
                     'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
            $day = $days[$date->englishDayOfWeek] ?? $date->englishDayOfWeek;

            return "{$day} à {$time}";
        }

        $months = ['Jan' => 'jan.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
                   'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
                   'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.'];
        $month = $months[$date->format('M')] ?? $date->format('M');

        return $date->format('j') . " {$month} à {$time}";
    }
}
