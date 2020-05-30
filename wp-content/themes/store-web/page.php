<?php
/**
 * Created by PhpStorm.
 * User: kingblanc
 * Date: 2020/5/30
 * Time: 22:02
 */
$page = $_GET['page'];

if (is_null($page)) {
    include dirname(__FILE__) . '/index.php';
} else {
    try {
        include_once dirname(__FILE__) . '/' .$_GET['page'].'.php';
    } catch (Exception $e){
        echo 'page is not exist!!!';
    }
}