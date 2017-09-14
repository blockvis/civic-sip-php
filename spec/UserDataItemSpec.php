<?php

namespace spec\Blockvis\Civic\Sip;

use Blockvis\Civic\Sip\UserDataItem;
use PhpSpec\ObjectBehavior;

class UserDataItemSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('label', 'value', true, false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserDataItem::class);
    }

    public function it_is_readable()
    {
        $this->label()->shouldBe('label');
        $this->value()->shouldBe('value');
        $this->isValid()->shouldBe(true);
        $this->isOwner()->shouldBe(false);
    }
}
