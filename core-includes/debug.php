<?php
class Fnt_Debug {
    static function debug_console($value = '', $stop_process = 0){
        echo '<pre>';
        print_r($value);
        echo '</pre>';
        if($stop_process == 1){
            die();
        }
    }

    static function debug_file($value = '', $header = ''){
        $date = date('Y/m/d H:i:s'). ': ';
        $location = ($header != '' ? $header.': ' : '');
        $valueToFile = json_encode($value);
        $fp = fopen(FNT_DIR_UPLOAD_BASE . '/fntfnt-debug-file.txt','a');
        fwrite($fp, $date. $location. $valueToFile."\r\n");
        fclose($fp);
    }

    static function debug_file_2($value, $die=0, $debug_type='',$header=''){
        if($debug_type == 'print'){
            $date = date('Y/m/d H:i:s'). ': ';
            $location = ($header != '' ? $header.': ' : '');
            $valueToFile = json_encode($value);
            $fp = fopen(FNT_DIR_UPLOAD_BASE. '/fntfnt-debug-file.txt','a');
            fwrite($fp, $date. $location. $valueToFile."\r\n");
            fclose($fp);
        }else{
            echo '<pre>';
            print_r($value);
            echo '</pre>';
        }
        if($die == 1){
            die();
        }
    }
}