<?php

function get_x_pos_min(int $unix_ts) :int {
    $day = date('d', $unix_ts);
    
    $year = date('Y', $unix_ts);
    
    $month = date('n', $unix_ts);
    
    $day_ts = mktime(0, 0, 0, $month, $day, $year);
    
    $morning_open_ts = mktime(9, 30, 0, $month, $day, $year);
    $morning_close_ts = mktime(12, 0, 0, $month, $day, $year);
    $afternoon_open_ts = mktime(13, 0, 0, $month, $day, $year);
    $afternoon_close_ts = mktime(16, 0, 0, $month, $day, $year);
    
    if ($unix_ts < $morning_open_ts) {
        $min_ts = $morning_open_ts;
        
    } else if ($unix_ts >= $morning_close_ts && $unix_ts <= $afternoon_open_ts) {
        $min_ts = $morning_close_ts;
        
    } else if ($unix_ts >= $afternoon_close_ts) {
        $min_ts = $afternoon_close_ts;
        
    } else {
        $min_ts = $unix_ts + 60 - $unix_ts % 60;
        
    }
    
    return $min_ts;
}