<?php

namespace xjryanse\xcache;

class Cache 
{
    //缓存目录
    private static $cacheDir;
    //缓存文件
    private static $cacheFile;
    //缓存时间
    private static $cacheTime;
    //初始化
    public static function init($cache_dir, $cache_time = 600) {
        self::$cacheDir = $cache_dir;
        self::$cacheTime = $cache_time;
        self::$cacheFile = $cache_dir . '/xcache.scache';
    }
    //取缓存
    public static function get($key, $default = '') {

        $data = self::readAndRender();
        self::checkTimeoutAndSave($data);

        return isset($data[$key]) 
            ?  $data[$key]['value']
            : $default ;
    }
    //设缓存
    public static function set($key, $value, $time = false) {
        if (!$time){
            $time = self::$cacheTime;
        }

        $data = self::readAndRender();
        $data[$key] = ['value' => $value, 'time' => time() + $time];

        return self::checkTimeoutAndSave($data);
    }

    private static function readAndRender() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir);
        }

        if (file_exists(self::$cacheFile)) {
            $json = file_get_contents(self::$cacheFile);
            $data = json_decode($json, true);
            if (!is_array($data)) {
                $data = [];
            }
        } else {
            $data = [];
        }

        return $data;
    }
    
    private static function checkTimeoutAndSave(&$data) {
        $cur_time = time();
        foreach ($data as $k => $v) {
            if ($cur_time > $data[$k]['time']) {
                unset($data[$k]);
            }
        }

        $content    = json_encode($data);
        $res        = file_put_contents(self::$cacheFile, $content);
        return $res ? true : false;
    }

}
