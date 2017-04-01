<?php
/**
 * Created by PhpStorm.
 * User: wxs77577 <wxs77577@gmail.com>
 * Date: 2017/3/31
 * Time: 16:03
 */

function get_working_hours($ini_str,$end_str){
    //config
    $ini_time = WH_BEGIN; //hr, min
    $end_time = WH_END; //hr, min
    //date objects
    $ini = date_create($ini_str);
    $ini_wk = date_time_set(date_create($ini_str),$ini_time[0],$ini_time[1]);
    $end = date_create($end_str);
    $end_wk = date_time_set(date_create($end_str),$end_time[0],$end_time[1]);
    //days
    $workdays_arr = get_workdays($ini,$end);
    $workdays_count = count($workdays_arr);
    $workday_seconds = (($end_time[0] * 60 + $end_time[1]) - ($ini_time[0] * 60 + $ini_time[1])) * 60;
    //get time difference
    $ini_seconds = 0;
    $end_seconds = 0;
    if(in_array($ini->format('Y-m-d'),$workdays_arr)) $ini_seconds = $ini->format('U') - $ini_wk->format('U');
    if(in_array($end->format('Y-m-d'),$workdays_arr)) $end_seconds = $end_wk->format('U') - $end->format('U');
    $seconds_dif = $ini_seconds > 0 ? $ini_seconds : 0;
    if($end_seconds > 0) $seconds_dif += $end_seconds;
    //final calculations
    $working_seconds = ($workdays_count * $workday_seconds) - $seconds_dif;
    echo $ini_str.' - '.$end_str.'; Working Hours:'.($working_seconds / 3600).PHP_EOL;
    return $working_seconds / 3600; //return hrs
}

function get_workdays(DateTime $ini,DateTime $end){
    //config
    $skipdays = WH_SKIP_DAYS; //saturday:6; sunday:0
    $skipdates = WH_SKIP_DATES; //eg: ['2016-10-10'];
    $workingdays = WH_WORK_DATES; //eg: ['2016-10-10'];
    //vars
    $current = clone $ini;
    $current_disp = $current->format('Y-m-d');
    $end_disp = $end->format('Y-m-d');
    $days_arr = [];
    //days range
    while($current_disp <= $end_disp){
        if(!in_array($current->format('w'),$skipdays) && !in_array($current_disp,$skipdates) || in_array($current_disp, $workingdays)){
            $days_arr[] = $current_disp;
        }
        $current->add(new DateInterval('P1D')); //adds one day
        $current_disp = $current->format('Y-m-d');
    }
    return $days_arr;
}