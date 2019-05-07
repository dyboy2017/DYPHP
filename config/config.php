<?php
/**
 * 全局配置文件
 */

// 数据库配置
$config['db']['host'] = 'localhost';
$config['db']['name'] = 'dyphpdb';
$config['db']['user'] = 'root';
$config['db']['pass'] = 'root';
$config['db']['port'] = 3306;

// 默认控制器和操作名
$config['defaultController'] = 'Item';
$config['defaultAction'] = 'index';

return $config;