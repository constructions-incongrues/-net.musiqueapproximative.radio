<?php

namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Combine
{
    private $parameters;

    public function __construct($parameters = [])
    {
        $this->parameters = array_merge(
            ['workingDirectory' => sys_get_temp_dir(), 'outputFilename' => 'out.mp3'],
            $parameters
        );
    }

    public function filter(Playlist $playlist)
    {
        $fs = new Filesystem();
        $playlist->each(function(AudioFile $audioFile, $i) use ($fs) {
            $fs->copy(
                $audioFile->getFile()->getRealpath(),
                sprintf('%s/%s.mp3', $this->parameters['workingDirectory'], $i)
            );
        });

        $fs->remove($this->parameters['outputFilename']);
        $command = sprintf(
            'sox -V1 $(ls -v %s/*.mp3) %s',
            $this->parameters['workingDirectory'],
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
