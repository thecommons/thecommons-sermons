<?php
/**
 * Created by PhpStorm.
 * User: alistair
 * Date: 9/14/14
 * Time: 3:22 PM
 */

namespace TheCommons\Sermons;

require_once('SermonSeries.php');
require_once('Sermon.php');
require_once('../libs/spyc/Spyc.php');

use JsonSerializable;
use SimpleXMLElement;
use Spyc;

class Sermons implements JsonSerializable
{
    private $mediaDir;
    private $webRoot;
    private $podcastTitle;
    private $podcastSubtitle;
    private $podcastAuthor;
    private $podcastEmail;
    private $podcastImage;
    private $podcastDescription;
    private $podcastLink;
    private $podcastLanguage;
    private $podcastCopyright;
    private $podcastCategory;


    private $series;

    public
    function __construct() {
        $this->parseConfig();
        $this->populateSeries();
    }

    public
    function getMediaDir() {
        return $this->mediaDir;
    }

    public
    function getWebPrefix()
    {
        return $this->webRoot;
    }

    public
    function getPodcastTitle()
    {
        return $this->podcastTitle;
    }

    public
    function getPodcastSubtitle()
    {
        return $this->podcastSubtitle;
    }

    public
    function getPodcastAuthor()
    {
        return $this->podcastAuthor;
    }

    public
    function getPodcastEmail()
    {
        return $this->podcastEmail;
    }

    public
    function getPodcastImage()
    {
        return $this->getWebPrefix() . '/' . $this->podcastImage;
    }

    public
    function getPodcastDescription()
    {
        return $this->podcastDescription;
    }

    public
    function getPodcastLink()
    {
        return $this->podcastLink;
    }

    public
    function getPodcastLanguage()
    {
        return $this->podcastLanguage;
    }

    public
    function getPodcastCopyright()
    {
        return $this->podcastCopyright;
    }

    public
    function getPodcastCategory()
    {
        return $this->podcastCategory;
    }

    public
    function parseConfig()
    {
        // find and parse the series.yml file
        // if there is no series.yml file...
        // throw an exception
        $yml = Spyc::YAMLLoad("config.yml");

        if (!$yml || !$yml['media-path']) {
            throw new \InvalidArgumentException("Missing config.yml");
        }

        // TODO: better error handling on missing elements

        $this->mediaDir = $yml['media-path'];
        $this->webRoot = $yml['public-media-path'];
        $this->podcastTitle = $yml['podcast-title'];
        $this->podcastSubtitle = $yml['podcast-subtitle'];
        $this->podcastAuthor = $yml['podcast-author'];
        $this->podcastEmail = $yml['podcast-email'];
        $this->podcastImage = $yml['podcast-image'];
        $this->podcastDescription = $yml['podcast-description'];
        $this->podcastLink = $yml['podcast-link'];
        $this->podcastLanguage = $yml['podcast-language'];
        $this->podcastCopyright = $yml['podcast-copyright'];
        $this->podcastCategory = $yml['podcast-category'];
    }

    public
    function populateSeries() {
        $this->series = [];

        // for each directory <series-name> in MEDIA_PATH
        foreach(glob($this->getMediaDir().'/*', GLOB_ONLYDIR) as $seriesDir) {
            if($seriesDir == '.' || $seriesDir == '..') {
                continue;
            }
            $id = basename($seriesDir);

            $this->series[] = new SermonSeries($id, $seriesDir,
                $this->getWebPrefix());
        }
    }

    /**
     * @return SermonSeries
     */
    public
    function getSeries() {
        return $this->series;
    }

    private
    function xmlEncode($str) {
        $str = str_replace('&', '&amp;', $str);
        $str = str_replace("'", '&apos;', $str);
        $str = str_replace('"', '&quot;', $str);
        $str = str_replace('<', '&lt;', $str);
        $str = str_replace('>', '&gt;', $str);
        return $str;
    }

    private
    function addTag($xmlStr, $tagName, $content, $encode=true) {
        if (!$xmlStr) {
            $xmlStr = "";
        }

        if($encode) {
            $content = $this->xmlEncode($content);
        }

        $xmlStr .= '<'.$tagName.'>'.$content.'</'.$tagName.'>';
        return $xmlStr;
    }

    public
    function getXml() {
        // man, i really hate xml...

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"' .
            ' version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' .
            '<channel>';

        $xmlStr = $this->addTag($xmlStr, 'title', $this->getPodcastTitle());
        $xmlStr = $this->addTag($xmlStr, 'itunes:subtitle',
            $this->getPodcastSubtitle());
        $xmlStr = $this->addTag($xmlStr, 'itunes:author',
            $this->getPodcastAuthor());
        $xmlStr .= '<itunes:owner><itunes:email>'.$this->getPodcastEmail().'</itunes:email></itunes:owner>';
        $xmlStr .= '<itunes:image href="'. $this->getPodcastImage() .'"/>';
        $xmlStr = $this->addTag($xmlStr, 'itunes:summary',
            $this->getPodcastDescription());
        $xmlStr = $this->addTag($xmlStr, 'description',
            $this->getPodcastDescription());
        $xmlStr = $this->addTag($xmlStr, 'link', $this->getPodcastLink());
        $xmlStr = $this->addTag($xmlStr, 'language',
            $this->getPodcastLanguage());
        $xmlStr = $this->addTag($xmlStr, 'copyright',
            $this->getPodcastCopyright());
        $xmlStr = $this->addTag($xmlStr, 'itunes:explicit', 'no');
        $xmlStr .= '<itunes:category text="'.$this->xmlEncode($this->getPodcastCategory()).'"/>';

        /** @var SermonSeries $series */
        foreach ($this->getSeries() as $series) {
            /** @var Sermon $sermon */
            foreach ($series->getSermons() as $sermon) {
                if (!$sermon->getAudio()) {
                    continue;
                }
                $xmlStr .= '<item>';

                $xmlStr = $this->addTag($xmlStr, 'guid', $sermon->getAudio());
                $xmlStr = $this->addTag($xmlStr, 'title',
                    $series->getTitle() . ': ' . $sermon->getTitle());

                // todo make this point to the sermon browser when it exists
                $xmlStr = $this->addTag($xmlStr, 'link',
                    $this->getPodcastLink());

                $xmlStr = $this->addTag($xmlStr, 'itunes:author',
                    $sermon->getAuthor());

                $xmlStr = $this->addTag($xmlStr, 'description',
                    '<![CDATA[' . $sermon->getDesc() . ']]>', false);

                $xmlStr = $this->addTag($xmlStr, 'itunes:summary',
                    '<![CDATA[' . $sermon->getDesc() . ']]>', false);

                $xmlStr = $this->addTag($xmlStr, 'pubDate',
                    date('r', $sermon->getTime()));

                $xmlStr = $this->addTag($xmlStr, 'itunes:duration',
                    $sermon->getAudioDuration());

                $xmlStr .= '<enclosure url="'.$sermon->getAudio().'" '.
                    'length="'. $sermon->getAudioBytes() .'" '.
                    'type="'. $sermon->getAudioType() .'" />';

                $xmlStr .= '</item>';
            }
        }

        $xmlStr .= '</channel>' .
            '</rss>';

        // parsing to get the xml to be pretty-printed
        $sxml = new \SimpleXMLElement($xmlStr);
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($sxml->asXML());

        return $dom->saveXML();
    }

    public function jsonSerialize()
    {
        return [
            'podcast-title' => $this->getPodcastTitle(),
            'podcast-subtitle' => $this->getPodcastSubtitle(),
            'podcast-author' => $this->getPodcastAuthor(),
            'podcast-image' => $this->getPodcastImage(),
            'podcast-desc' => $this->getPodcastDescription(),
            'podcast-link' => $this->getPodcastLink(),
            'podcast-language' => $this->getPodcastLanguage(),
            'podcast-copyright'=> $this->getPodcastCopyright(),
            'podcast-category' => $this->getPodcastCategory(),
            'series' => $this->getSeries(),
        ];
    }
}
