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

    function it_can_return_file_md5sum()
    {
        $this->getMd5()->shouldBeString();
    }

    function it_can_return_title()
    {
        $this->getTitle()->shouldBeString();
    }

    function it_can_return_artist()
    {
        $this->getArtist()->shouldBeString();
    }

    function it_can_return_description()
    {
        $this->getDescription()->shouldBeString();
    }
}
