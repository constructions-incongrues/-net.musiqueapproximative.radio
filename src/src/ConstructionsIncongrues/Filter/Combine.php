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
        $fs = new Filesystem();

        // Copy tracks to dedicated working directory
        $playlist->mirrorTo($this->getParameters()['workingDirectory']);

        $fs->remove($this->parameters['outputFilename']);
        $files = glob(sprintf('%s/*.mp3', $this->getParameters()['workingDirectory']));
        $strFiles = [];
        foreach ($files as $file) {
            $strFiles[] = sprintf('"%s"', $file);
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
