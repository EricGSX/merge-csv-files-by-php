<?php



require_once './hours.php';
define('WH_BEGIN', [9,30]);
define('WH_END', [18,0]);
define('WH_SKIP_DAYS', []);
 define('WH_SKIP_DATES', []);
 define('WH_WORK_DATES', []);

//get_working_hours('2017-03-31 09:30:00', '2017-04-01 16:30:00');

/**
 * 合并csv
 */
function mergeCsv($filename, $fw)
{
    // 转入临时文件
    @file_put_contents('./tmp.csv', iconv('utf-8', 'gbk//ignore', file_get_contents($filename)));

    $fp = fopen('tmp.csv', 'r');
    $rows = [];
    $i = 0;
    while (($row = fgetcsv($fp)) && ++$i < 50) {
        // 获取部门和月份
        $row[]=explode('/',$filename)[2] ;
        $row[]=explode('/',$filename)[3] ;
        $row[]=explode('.',explode('/',$filename)[4])[0] ;

        // 计算工时
        if($row[4] != '' && $row[9] !=''){
            $begin = date('Y-m-d H:i:s',strtotime($row[4]));
            $end = date('Y-m-d H:i:s',strtotime($row[9]));
           $row[] = round(get_working_hours($begin,$end),1);

        }else{
            $row[] = '/';
        }
        $rows[] = $row;

    }
    fclose($fp);
    foreach ($rows as $k=>$row) {
        // 删除标题行
        if($k>0){
            fputcsv($fw, $row);
        }
    }
}

/**
 * 遍历文件夹，合并文件
 * @param $dir
 * @return array
 */
function tree($dir, $fw)
{
    static $arr = array();
    if (is_dir($dir)) {
        $hadle = @opendir($dir);
        while ($file = readdir($hadle)) {
            if (substr($file,0,1) != '.') {
                // 拼接文件路径
                $dirr = $dir . '/' . $file;
                // 追加到静态数组
                array_push($arr, $dirr);
                if (is_dir($dirr)) {
                    // 写入文件夹名
//                    fputcsv($fw, [$file]);
                    tree($dirr, $fw);
                } else {
                    // 写入
//                    fputcsv($fw, [$file]);
                    mergeCsv($dirr, $fw);
                }
            }
        }
    }
    return $arr;
}

// 创建输出文件
$fw = fopen('./output.csv', 'a');


// 写入标题行
$top = '标题,备注,优先级,执行者,开始时间,截止时间,创建者,创建时间,是否完成,完成时间,分组,列表,子任务,延误天数,已延误,标签,部门,月份,模块,工时(小时)';
fputcsv($fw,explode(',',iconv('utf-8', 'gbk//ignore',$top)));

// 执行
print_r(tree('./files', $fw));

// 关闭
fclose($fw);

// 删除临时文件
unlink('./tmp.csv');