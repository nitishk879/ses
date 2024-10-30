<?php

namespace App\Http\Traits;

trait FormatNumberTrait
{
    /**
     * Let's format Numbers
     *
     * @param $number
     * @return string
     */
    public function formatNumber($number): string
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B'; // Billions
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M'; // Millions
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K'; // Thousands
        } else {
            return $number; // Less than 1000, no abbreviation
        }
    }

}
