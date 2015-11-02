<?php

namespace ConstructionsIncongrues\PlaylistRenderer;

use ConstructionsIncongrues\Entity\Playlist;

interface PlaylistRendererInterface
{
    public function render(Playlist $playlist, array $options = []);
}
