<?php

namespace spec\ConstructionsIncongrues\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ConstructionsIncongrues\Entity\Playlist;
use ConstructionsIncongrues\Entity\AudioFile;
use Symfony\Component\Filesystem\Filesystem;

class SilenceSpec extends ObjectBehavior
{
    function let($workingDirectory)
    {
        $fs = new Filesystem();
        $workingDirectory = tempnam(sys_get_temp_dir(), 'spec_silence');
        $fs->mkdir($workingDirectory);
    }

    function letgo($workingDirectory)
    {
        var_dump($workingDirectory);
        $fs = new Filesystem();
        $fs->remove($workingDirectory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\Filter\Silence');
    }

    function it_removes_silences_from_beginning_of_tracks($workingDirectory)
    {
        $playlist = new Playlist();
        $playlist->push(new AudioFile(new \SplFileInfo(__DIR__.'/../../../fixtures/filters/silence_beginning.mp3')));
        $playlist = $playlist->mirrorTo($workingDirectory);
        var_dump($playlist[0]->getDuration());
        $this->filter($playlist)->shouldReturnAnInstanceOf('ConstructionsIncongrues\Entity\Playlist');
        var_dump($playlist[0]->getDuration());
        $fs = new Filesystem();
        $fs->remove($workingDirectory);
    }

    function it_removes_silences_from_ending_of_tracks($workingDirectory)
    {
        $playlist = new Playlist();
        $playlist->push(new AudioFile(new \SplFileInfo(__DIR__.'/../../../fixtures/filters/silence_ending.mp3')));
        $playlist = $playlist->mirrorTo($workingDirectory);
        var_dump($playlist[0]->getDuration());
        $this->filter($playlist)->shouldReturnAnInstanceOf('ConstructionsIncongrues\Entity\Playlist');
        var_dump($playlist[0]->getDuration());
        $fs = new Filesystem();
        $fs->remove($workingDirectory);
    }
}
