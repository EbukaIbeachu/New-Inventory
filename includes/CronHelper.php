<?php

class CronHelper {
    public static function isDue($expression) {
        // Simple Cron Parser (Supports * and specific numbers)
        // Format: Minute Hour Day Month DayOfWeek
        $parts = preg_split('/\s+/', $expression);
        if (count($parts) != 5) return false;

        $now = explode(' ', date('i G j n w')); // Min, Hour, Day, Month, Weekday (0-6)
        
        for ($i = 0; $i < 5; $i++) {
            if ($parts[$i] === '*') continue;
            
            // Handle lists (e.g., 1,2,3)
            if (strpos($parts[$i], ',') !== false) {
                $options = explode(',', $parts[$i]);
                if (!in_array($now[$i], $options)) return false;
                continue;
            }
            
            // Handle step values (e.g., */5) - Simplified: only if starts with *
            if (strpos($parts[$i], '*/') === 0) {
                $step = (int)substr($parts[$i], 2);
                if ($step > 0 && ($now[$i] % $step) !== 0) return false;
                continue;
            }

            if ($parts[$i] != $now[$i]) return false;
        }

        return true;
    }
}
