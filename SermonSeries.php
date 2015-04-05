<?php
/**
 * Created by PhpStorm.
 * User: alistair
 * Date: 9/14/14
 * Time: 3:37 PM
 */

namespace TheCommons\Sermons;

require_once('Sermon.php');
require_once('../libs/spyc/Spyc.php');

use JsonSerializable;
use Spyc;

class SermonSeries implements JsonSerializable {

    private $id;
    private $path;
    private $webPath;
    private $title;
    private $desc;
    private $cover_bg;
    private $cover_fg;
    private $video;

    private $sermons;

    public
    function __construct($id, $path, $prefix) {
        $this->id = $id;
        $this->path = $path;
        $this->webPath = $prefix . '/' . $id;

        $this->parseSeriesInfo($path);
        $this->populateSermons();
    }

    public
    function getId() {
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
    function getTitle() {
        return $this->title;
    }

    public
    function getDesc()
    {
        return $this->desc;
    }

    public
    function getCoverBG() {
        return $this->getWebPath() . '/' .$this->cover_bg;
    }

    public
    function getCoverBG() {
        return $this->getWebPath() . '/' .$this->cover_fg;
    }

    public
    function getVideo()
    {
        return $this->video;
    }

    /**
     * @return Sermon
     */
    public
    function getSermons() {
        return $this->sermons;
    }

    public
    function parseSeriesInfo($path) {
        // find and parse the series.yml file
        // if there is no series.yml file...
        // throw an exception
        $seriesYml = Spyc::YAMLLoad($path . "/series.yml");

        if(!$seriesYml || !$seriesYml['series-title']) {
            throw new \InvalidArgumentException("Missing series.yml for " .
                $path);
        }

        $this->title = $seriesYml['series-title'];
        $this->desc = $seriesYml['series-desc'];
        $this->cover_bg = $seriesYml['series-cover-bg'];
	$this->cover_fg = $seriesYml['series-cover-fg'];
        $this->video = $seriesYml['series-video'];
    }

    public
    function populateSermons() {
        $this->sermons = [];

        foreach (glob($this->getPath() . '/*', GLOB_ONLYDIR) as $sermonDir) {
            if ($sermonDir == '.' || $sermonDir == '..') {
                continue;
            }
            $id = basename($sermonDir);

            $this->sermons[] = new Sermon($id, $sermonDir,
                $this->getWebPath());
        }
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'sermon-series',
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'cover_bg' => $this->getCoverBG(),
	    'cover_fg' => $this->getCoverFG(),
            'video' => $this->getVideo(),
            'sermons' => $this->getSermons(),
        ];
    }

} 