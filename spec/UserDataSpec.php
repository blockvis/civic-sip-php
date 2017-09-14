<?php

namespace spec\Blockvis\Civic\Sip;

use Blockvis\Civic\Sip\UserData;
use Blockvis\Civic\Sip\UserDataItem;
use PhpSpec\ObjectBehavior;

class UserDataSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('userId', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserData::class);
    }

    function it_returns_user_id()
    {
        $this->userId()->shouldBe('userId');
    }

    function it_returns_data_items()
    {
        $items = [
            new UserDataItem('label1', 'value1', true, true),
            new UserDataItem('label2', 'value2', true, false),
        ];

        $this->beConstructedWith('userId', $items);
        $this->items()->shouldBe($items);
    }

}
