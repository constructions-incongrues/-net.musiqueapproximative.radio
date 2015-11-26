<?php

namespace spec\ConstructionsIncongrues\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NormalizeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\Filter\Normalize');
    }
}
