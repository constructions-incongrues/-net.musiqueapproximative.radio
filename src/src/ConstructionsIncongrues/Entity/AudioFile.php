<?php

namespace ConstructionsIncongrues\Entity;

use Jasny\Audio\Track;

class AudioFile
{
    private $file;
    private $stats = [];

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

    public function resetStats()
    {
        $this->stats = null;
    }
}
