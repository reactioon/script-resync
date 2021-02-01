<?php


if (!function_exists('tickSize')) {

    function tickSize($d) {
        $s = str_replace("0.","",$d);
        $l = strlen($s);
        return $l;
    }

}

if (!function_exists('secondsToTime')) {

    function secondsToTime($s) {
        $h = floor($s / 3600);
        $s -= $h * 3600;
        $m = floor($s / 60);
        $s -= $m * 60;
        return $h.':'.sprintf('%02d', $m).':'.sprintf('%02d', $s);
    }

}

if (!function_exists('reorderSnapshotFiles')) {

    function reorderSnapshotFiles($a, $b) {

        if ($a != ".." && $a != ".") {

            $expNameA = explode("-", $a);
            $expNameB = explode("-", $b);

            $ka = str_replace(".json","", $expNameA[count($expNameA)-1]);
            $kb = str_replace(".json","", $expNameB[count($expNameB)-1]);

            // if ($expNameA == $expNameB) {
            //     return false;
            // }

            if ($kb == "." || $kb == "..") { return false; }
            if ($ka == "." || $ka == "..") { return false; }

            return ($ka < $kb) ? false : true;

        }

    }

}

if (!function_exists('getLastDayOfMonth')) {

    function getLastDayOfMonth($month, $year) {

        if (is_numeric($month) && is_numeric($year)) {

            return idate('d', mktime(0, 0, 0, ($month + 1), 0, $year));

        }

    }

}

?>