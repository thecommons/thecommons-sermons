<?php
/**
 * Created by PhpStorm.
 * User: alistair
 * Date: 9/14/14
 * Time: 3:37 PM
 */

namespace TheCommons\Sermons;


use JsonSerializable;
use Spyc;

class Sermon implements JsonSerializable
{

    private $id;
    private $path;
    private $webPath;
    private $title;
    private $author;
    private $time;
    private $desc;
    private $audio;

    public
    function __construct($id, $path, $prefix)
    {
        $this->id = $id;
        $this->path = $path;
        $this->webPath = $prefix . '/' . $id;

        $this->parseSermonInfo($path);
    }

    public
    function getId()
    {
        return $this->id;
    }

    public
    function getPath()
    {
        return $this->path;
    }

    public
    function getWebPath()
    {
        return $this->webPath;
    }

    public
    function getTitle()
    {
        return $this->title;
    }

    public
    function getAuthor()
    {
        return $this->author;
    }

    public
    function getTime()
    {
        return $this->time;
    }

    public
    function getAudio()
    {
        return $this->getWebPath() . '/' . $this->audio;
    }

    public
    function getAudioDuration()
    {
        return 0;
    }

    public
    function getAudioBytes()
    {
        return filesize($this->getPath() . '/' .$this->audio);
    }

    public
    function getAudioType()
    {
        return 'audio/mpeg';
    }

    public
    function getDesc()
    {
        return $this->desc;
    }

    public
    function parseSermonInfo($path)
    {
        // find and parse the series.yml file
        // if there is no series.yml file...
        // throw an exception
        $sermonYml = Spyc::YAMLLoad($path . "/sermon.yml");

        if (!$sermonYml || !$sermonYml['sermon-title']) {
            throw new \InvalidArgumentException("Missing sermon.yml for " .
                $path);
        }

        $this->title = $sermonYml['sermon-title'];
        $this->desc = $sermonYml['sermon-desc'];
        $this->time = strtotime($sermonYml['sermon-time']);
        $this->audio = $sermonYml['sermon-audio'];
        $this->author = $sermonYml['sermon-author'];
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'sermon',
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'time' => $this->getTime(),
            'audio' => $this->getAudio(),
            'desc' => $this->getDesc(),
        ];
    }

} 