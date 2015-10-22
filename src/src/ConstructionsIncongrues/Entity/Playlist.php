<?php

namespace ConstructionsIncongrues\Entity;

use Illuminate\Support\Collection;
use Jasny\Audio\Track;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Playlist extends Collection
{
    private $duration;

    public function getDuration()
    {
        $this->duration = 0;
        foreach ($this->all() as $audioFile) {
            $this->duration += $audioFile->getDuration();
        }
        return $this->duration;
    }

    public function reset()
    {
        foreach ($this->all() as $audioFile) {
            $audioFile->reset();
        }
    }

    public function __toString()
    {
        $playlist = [];
        foreach ($this->all() as $audioFile) {
            $playlist[] = sprintf('%s - %s (%s)', $audioFile->getArtist(), $audioFile->getTitle(), $audioFile->getDuration());
        }

        return sprintf(
            "%s\n\n%d files\n%s seconds\n",
            implode("\n", $playlist),
            count($playlist),
            $this->getDuration()
        );
    }

    public function shrinkTo($limit)
    {
        while ($this->getDuration() > $limit) {
            $this->pop();
        }

        return $this->getDuration();
    }

    public function mirrorTo($directory)
    {
        // Copy playlist files to working directory
        $fs = new Filesystem();
        $this->each(function($audioFile, $i) use ($fs, $directory) {
            $fileDestination = sprintf('%s/%s', $directory, $audioFile->getFile()->getFilename());
            $fs->copy($audioFile->getFile()->getRealpath(), $fileDestination);
            $audioFile->setFile(new \SplFileInfo($fileDestination));
        });

        return $this;
    }
}
