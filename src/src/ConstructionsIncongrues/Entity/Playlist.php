<?php

namespace ConstructionsIncongrues\Entity;

use Illuminate\Support\Collection;

class Playlist extends Collection
{
    private $duration;

    public function getDuration()
    {
        if ($this->duration === null) {
            $iterator = $this->getIterator();
            while ($iterator->valid()) {
                $this->duration += $this[$iterator->key()]->getDuration();
                $iterator->next();
            }
        }

        return $this->duration;
    }

    public function __toString()
    {
        $playlist = [];
        foreach ($this->all() as $audioFile) {
            $playlist[] = $audioFile->getFile()->getFilename();
        }

        return sprintf("%s\n\n%d files\n", implode("\n", $playlist), count($playlist));
    }
}
