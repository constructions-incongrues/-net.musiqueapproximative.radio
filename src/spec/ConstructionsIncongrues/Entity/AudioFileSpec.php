<?php

namespace spec\ConstructionsIncongrues\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AudioFileSpec extends ObjectBehavior
{
    function let(\SplFileInfo $file)
    {
        $this->beConstructedWith($file);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\Entity\AudioFile');
    }

    function it_can_return_duration()
    {
        $this->getDuration()->shouldBeInteger();
    }

    function it_can_return_file($file)
    {
        $this->getFile()->shouldReturnAnInstanceOf($file);
    }

    function stats_can_be_reset()
    {
    }
}
