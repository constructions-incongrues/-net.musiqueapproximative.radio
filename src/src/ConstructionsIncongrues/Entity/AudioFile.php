<?php

namespace ConstructionsIncongrues\Entity;

class AudioFile
{
    private $file;
    private $duration;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function getDuration()
    {
        if ($this->duration === null) {
            $duration = 'TODO';
            $this->duration = $duration;
        }
        return $this->duration;
    }

    public function getFile()
    {
        return $this->file;
    }

    private function calculateDuration()
    {
        // @see https://github.com/jasny/audio
    }
}
