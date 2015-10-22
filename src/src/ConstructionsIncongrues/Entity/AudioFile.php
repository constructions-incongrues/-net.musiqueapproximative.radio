<?php

namespace ConstructionsIncongrues\Entity;

use Jasny\Audio\Track;

class AudioFile
{
    private $file;
    private $md5sum;
    private $stats = [];
    private $title;
    private $artist;
    private $description;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function getDuration()
    {
        if (!isset($this->stats['length'])) {
            $track = new Track($this->getFile()->getRealpath());
            $this->stats = get_object_vars($track->getStats());
        }
        return $this->stats['length'];
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(\SplFileInfo $file)
    {
        $this->file = $file;
        $this->reset();
    }

    public function reset()
    {
        $this->stats = null;
        $this->md5sum = null;
    }

    public function getMd5()
    {
        if (!isset($this->md5sum)) {
            $this->md5sum = md5_file($this->getFile()->getRealpath());
        }
        return $this->md5sum;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getArtist()
    {
        return $this->artist;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setArtist($artist)
    {
        $this->artist = $artist;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
