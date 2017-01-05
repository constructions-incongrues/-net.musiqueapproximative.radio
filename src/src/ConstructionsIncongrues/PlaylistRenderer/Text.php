<?php

namespace ConstructionsIncongrues\PlaylistRenderer;

use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;

class Text implements PlaylistRendererInterface
{
    /**
     * Renders playlist as text.
     *
     * @param Playlist $playlist
     * @param array $options
     * @return string
     */
    public function render(Playlist $playlist, array $options = [])
    {
        // Layout
        $layout = <<<EOT
# Radio Approximative #%d sur %s (%s)

## Playlist

%%s

## Liens

- [Télécharger l'émission](http://radio.musiqueapproximative.net/shows/%s/%s.mp3)
- [Télécharger la playlist](http://radio.musiqueapproximative.net/shows/%s/%s.txt)

EOT;
        $template = sprintf(
            $layout,
            $options['number'],
            ucfirst($options['channel']),
            $options['date'],
            $options['channel'],
            $options['filename'],
            $options['channel'],
            $options['filename']
        );

        $list = [];
        if ($playlist->count()) {
            $timestamp = 0;
            /** @var Audiofile $audioFile */
            foreach ($playlist->all() as $audioFile) {
                $list[] = sprintf(
                    '- [%s] %s - %s',
                    $this->timestampToTimecode($timestamp),
                    $audioFile->getArtist(),
                    $audioFile->getTitle()
                );
                $timestamp += $audioFile->getDuration();
            }
        }

        return sprintf($template, implode("\n", $list));
    }

    /**
     * Converts timestamp to timecode.
     *
     * @param $timestamp
     * @return string
     */
    private function timestampToTimecode($timestamp)
    {
        $datetime = \DateTime::createFromFormat('H:i:s', '00:00:00');
        $datetime->add(new \DateInterval(sprintf('PT%sS', (int)$timestamp)));

        return $datetime->format('H:i:s');
    }
}
