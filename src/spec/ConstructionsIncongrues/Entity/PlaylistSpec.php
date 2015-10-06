<?php

namespace spec\ConstructionsIncongrues\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ConstructionsIncongrues\Entity\AudioFile;

class PlaylistSpec extends ObjectBehavior
{
    function let()
    {
        $this->push(new AudioFile(new \SplFileInfo('aa')));
        $this->push(new AudioFile(new \SplFileInfo('aa')));
        $this->push(new AudioFile(new \SplFileInfo('aa')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\Entity\Playlist');
    }

    function it_can_return_duration()
    {
        $this->getDuration()->shouldEqual(3);
    }
}
