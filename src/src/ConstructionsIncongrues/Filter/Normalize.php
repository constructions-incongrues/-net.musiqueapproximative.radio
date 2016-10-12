<?php

namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\Playlist;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Normalize extends AbstractFilter
{
    protected $name = 'normalize';

    public function __construct($parameters = [])
    {
        $parameters = array_merge(
            ['workingDirectory' => sys_get_temp_dir()],
            $parameters
        );
        parent::__construct($parameters);
    }

    public function filter(Playlist $playlist)
    {
        // Copy tracks to dedicated working directory
        $playlist->mirrorTo($this->getParameters()['workingDirectory']);

        // -r        apply Track gain automatically (all files set to equal loudness)
        // -k        automatically lower Track gain to not clip audio
        // -o        output is a database-friendly tab-delimited list
        // -s r      force re-calculation (do not read tag info)
        $command = sprintf('mp3gain -r -k -o -s r %s/*.mp3', $this->getParameters()['workingDirectory']);
        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $playlist;
    }
}
