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
            ['label' => 'label1', 'value' => 'value1', 'isValid' => true, 'isOwner' => true],
            ['label' => 'label2', 'value' => 'value2', 'isValid' => true, 'isOwner' => false],
        ];

        $this->beConstructedWith('userId', $items);
        $items = $this->items();
        $items[0]->label()->shouldBe('label1');
        $items[0]->value()->shouldBe('value1');
        $items[0]->isValid()->shouldBe(true);
        $items[0]->isOwner()->shouldBe(true);
        $items[1]->label()->shouldBe('label2');
        $items[1]->value()->shouldBe('value2');
        $items[1]->isValid()->shouldBe(true);
        $items[1]->isOwner()->shouldBe(false);
    }

}
