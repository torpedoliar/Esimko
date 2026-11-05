<?php

namespace App\Support;

class LoanApplicationRules
{
    // The IDs are also used by MonitoringController: long, short, goods.
    const MAX_TENOR = [9 => 60, 10 => 15, 11 => 20];

    public static function tenorError($type, $tenor, $allowExisting = false)
    {
        if (!is_scalar($type) || !preg_match('/^(9|10|11)$/D', (string) $type)) {
            return 'Jenis pinjaman tidak valid';
        }
        if (!is_scalar($tenor) || !preg_match('/^[1-9][0-9]*$/D', (string) $tenor)) {
            return 'Tenor harus berupa bilangan bulat positif';
        }
        $max = self::MAX_TENOR[(int) $type];
        if (!$allowExisting && (float) $tenor > $max) {
            return 'Tenor melebihi maksimal tenor yaitu '.$max.' bulan';
        }
        return null;
    }
}
