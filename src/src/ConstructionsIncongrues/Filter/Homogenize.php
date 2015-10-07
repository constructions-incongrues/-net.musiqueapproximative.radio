<?php

namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\Playlist;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Homogenize
{
    public function filter(Playlist $playlist)
    {
        foreach ($playlist->all() as $audioFile) {
            $fileTmp = sprintf('%s/homogenize_%s.mp3', $audioFile->getFile()->getPath(), uniqid());
            $fileOriginal = $audioFile->getFile()->getRealpath();
            $command = sprintf(
                'lame --silent --mp3input --resample 44.1 -m j "%s" "%s"',
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

        // Stats may have changed
        $playlist->resetStats();

        return $playlist;
    }
}
