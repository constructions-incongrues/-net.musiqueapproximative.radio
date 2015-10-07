<?php

namespace spec\ConstructionsIncongrues\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HomogenizeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ConstructionsIncongrues\Filter\Homogenize');
    }
}
