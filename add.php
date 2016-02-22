<?php
/**
 * Created by PhpStorm.
 * User: alistair
 * Date: 2/20/16
 * Time: 3:02 PM
 */

require_once('Sermons.php');

function response($result, $msg) {
    $data = [
        'result' => $result,
        'message' => $msg,
    ];

    header('Content-type: application/json');
    echo json_encode($data);
    return true;
}

$seriesId = $_POST['seriesId'];
$title = $_POST['title'];
$author = $_POST['author'];
$date = $_POST['date'];
$desc = $_POST['desc'];
// $audio = $_POST['audio'];

if (!$seriesId) {
    return response(false, 'Missing Series ID');
}
if (!$title) {
    return response(false, 'Missing Title');
}
if (!$author) {
    return response(false, 'Missing Author');
}
if (!$date) {
    return response(false, 'Missing Date');
}
if (!$desc) {
    return response(false, 'Missing Description');
}

// add the sermon

$sermonArchive = new \TheCommons\Sermons\Sermons();

$series = $sermonArchive->getSeriesByID($seriesId);

if (!$series) {
    return response(false, 'Could not find series ' . $seriesId);
}

if ($series->addSermon($title, $author, $date, $desc)) {
    return response(true, 'Sermon successfully added');
}

response(false, 'Could not add sermon');
