<?php

namespace spec\ConstructionsIncongrues\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CombineSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\Filter\Combine');
    }
}
