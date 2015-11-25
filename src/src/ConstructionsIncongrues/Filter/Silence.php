<?php

namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\Playlist;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Silence extends AbstractFilter
{
    protected $name = 'silence';

    public function filter(Playlist $playlist)
    {
        /** @var AudioFile $audioFile */
        foreach ($playlist->all() as $audioFile) {
            $fileTmp = sprintf('%s/silence_%s.mp3', $audioFile->getFile()->getPath(), uniqid());
            $fileOriginal = $audioFile->getFile()->getRealpath();
            $command = sprintf(
                'sox "%s" "%s" silence 1 0.1 0.1%% reverse silence 1 0.1 0.1%% reverse',
                $fileOriginal,
                $fileTmp
            );
            $process = new Process($command);
            $process->setTimeout(600);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            // // Move temp file to original name
            $fs = new Filesystem();
            $fs->remove($fileOriginal);
            $fs->rename($fileTmp, $fileOriginal);
        }

        // Durations may have changed
        $playlist->reset();

        return $playlist;
    }
}
