<?php

namespace spec\ConstructionsIncongrues\PlaylistRenderer;

use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TextSpec extends ObjectBehavior
{
    function let(Playlist $playlist)
    {
        $dirTracks = __DIR__ . '/../../../fixtures/tracks/real';
        $playlist->push(new AudioFile(new \SplFileInfo($dirTracks . '/001.mp3')));
        $playlist->push(new AudioFile(new \SplFileInfo($dirTracks . '/002.mp3')));
        $playlist->push(new AudioFile(new \SplFileInfo($dirTracks . '/003.mp3')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\PlaylistRenderer\Text');
    }

    function it_implements_PlaylistRendererInterface()
    {
        $this->shouldImplement('ConstructionsIncongrues\PlaylistRenderer\PlaylistRendererInterface');
    }

    function it_renders_playlist_as_text($playlist)
    {
        $this->render($playlist)->shouldReturn('PLAYLIST');
    }
}
