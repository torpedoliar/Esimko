<?php

use Illuminate\Support\Facades\Route;

function list_gender(){
    return ['L' => 'Laki-laki', 'P' => 'Perempuan'];
}

function remove_space($value)
{
    return str_replace(' ', '', $value);
}

function serialize_array($data)
{
    $result = [];
    foreach ($data as $key => $value) array_push($result, $key . "=" . $value);
    return join('&', $result);
}

function has_route($route)
{
    return Route::has($route) ? route($route) : '#';
}

function menu_active($route)
{
    $menu_active = session('menu_active', '');
    return $menu_active == $route ? 'active' : '';
}

function format_number($number, $currency = 'IDR')
{
    if ($number == "") return "0";
    return $currency == 'IDR' ?
        number_format($number, 0, ',', '.') :
        number_format($number, 2, '.', ',');
}

function format_decimal($number)
{
    if ($number == "") return "";
    return number_format($number, 2, ',', '.');
}

function list_bulan($short = false)
{
    return $short == true ?
        array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des') :
        array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
}

function list_hari()
{
    return array('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu');
}

function fulldate($date, $divider = " ", $dayEnable = false, $shortMonth = false)
{
    if ($date == "") return "";
    $dayText = list_hari();
    $monthText = list_bulan($shortMonth);

    $dayInt = date('N', strtotime($date));
    $date = explode("-", date('Y-m-d', strtotime($date)));
    $monthInt = (int)$date[1];

    $result = [];
    if ($dayEnable == true) array_push($result, $dayText[date('N', $dayInt)-1] . ', ');
    array_push($result, $date[2]);
    array_push($result, $monthText[$monthInt-1]);
    array_push($result, $date[0]);
    return join($divider, $result);
}

function format_date($date, $divider = "-")
{
    if ($date == "") return "";
    $date = explode("-", date('Y-m-d', strtotime($date)));
    return join($divider, [$date[2], $date[1], $date[0]]);
}

function format_time($time, $short = true)
{
    if ($time == "") return "";
    return $short == true ?
        date('H:i', strtotime($time)) :
        date('H:i:s', strtotime($time));
}

function number_to_alphabeth($number)
{
    $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return $map[$number-1] ?? '';
}

function number_to_roman($number)
{
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $result = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $result .= $roman;
                break;
            }
        }
    }
    return $result;
}

function roman_to_number($roman)
{
    $romans = array(
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    );

    $result = 0;
    foreach ($romans as $key => $value) {
        while (strpos($roman, $key) === 0) {
            $result += $value;
            $roman = substr($roman, strlen($key));
        }
    }
    return $result;
}

function spellNumberCore($nilai) {
    $nilai = abs($nilai);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    if ($nilai < 12) {
        $temp = " ". $huruf[$nilai];
    } else if ($nilai <20) {
        $temp = spellNumberCore($nilai - 10). " belas";
    } else if ($nilai < 100) {
        $temp = spellNumberCore($nilai/10)." puluh". spellNumberCore($nilai % 10);
    } else if ($nilai < 200) {
        $temp = " seratus" . spellNumberCore($nilai - 100);
    } else if ($nilai < 1000) {
        $temp = spellNumberCore($nilai/100) . " ratus" . spellNumberCore($nilai % 100);
    } else if ($nilai < 2000) {
        $temp = " seribu" . spellNumberCore($nilai - 1000);
    } else if ($nilai < 1000000) {
        $temp = spellNumberCore($nilai/1000) . " ribu" . spellNumberCore($nilai % 1000);
    } else if ($nilai < 1000000000) {
        $temp = spellNumberCore($nilai/1000000) . " juta" . spellNumberCore($nilai % 1000000);
    } else if ($nilai < 1000000000000) {
        $temp = spellNumberCore($nilai/1000000000) . " milyar" . spellNumberCore(fmod($nilai,1000000000));
    } else if ($nilai < 1000000000000000) {
        $temp = spellNumberCore($nilai/1000000000000) . " trilyun" . spellNumberCore(fmod($nilai,1000000000000));
    }
    return $temp;
}

function terbilang($number) {
    return $number > 0 ? trim(spellNumberCore($number)) : "minus ". trim(spellNumberCore($number));
}

function date_difference($date1, $date2)
{
    $tgl1 = new DateTime($date1);
    $tgl2 = new DateTime($date2);
    return $tgl2->diff($tgl1)->days + 1;
}

function unformat_date($date)
{
    if ($date == "") return '';
    return date('Y-m-d', strtotime($date));
}

function unformat_number($number)
{
    if ($number == "") return "";
    $number = str_replace(".", "", $number);
    $number = str_replace(",", ".", $number);
    return $number;
}
