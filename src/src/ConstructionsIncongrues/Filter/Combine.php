<?php

namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Combine extends AbstractFilter
{
    protected $name = 'combine';

    public function __construct($parameters = [])
    {
        $parameters = array_merge(
            ['workingDirectory' => sys_get_temp_dir(), 'outputFilename' => 'out.mp3'],
            $parameters
        );

        parent::__construct($parameters);
    }

    public function filter(Playlist $playlist)
    {

        // Copy tracks to dedicated working directory
        $playlist->mirrorTo($this->getParameters()['workingDirectory']);

        // Remove any previous output file to prevent appending
        $fs = new Filesystem();
        $fs->remove($this->parameters['outputFilename']);

        //

        $strFiles = [];
        /** @var AudioFile $audioFile */
        foreach ($playlist as $audioFile) {
            $strFiles[] = sprintf('"%s"', $audioFile->getFile()->getPathname());
        }
        $command = sprintf(
            'sox -V1 %s -C 320 %s',
            implode(' ', $strFiles),
            $this->parameters['outputFilename']
        );
        var_dump($command);
        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $playlistShow = new Playlist();
        $playlistShow->push(new AudioFile(new \SplFileInfo($this->parameters['outputFilename'])));

        return $playlistShow;
    }
}
