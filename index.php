<?php
/**
 * Created by PhpStorm.
 * User: alistair
 * Date: 9/14/14
 * Time: 2:42 PM
 */

require_once('Sermons.php');

$format = $_GET['format'] ? $_GET['format'] : 'xml';

if($format !== 'xml' && $format != 'json') {
    $response = 'Invalid format';
}

$sermons = new \TheCommons\Sermons\Sermons();

if($format === 'json') {
    header('Content-Type: application/json');
    $response = json_encode($sermons, JSON_PRETTY_PRINT);
} else {
    header('Content-Type: application/rss+xml; charset=utf-8');
    $response = $sermons->getXml();
}

echo $response;