<?php

namespace spec\Blockvis\Civic\Sip;

use Blockvis\Civic\Sip\UserDataItem;
use JsonSerializable;
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

    function it_is_json_serializable()
    {
    	$this->shouldImplement(JsonSerializable::class);
    	assert(json_encode($this->getWrappedObject()) === json_encode([
    		'label' => 'label',
		    'value' => 'value',
		    'isValid' => true,
		    'isOwner' => false,
		]), 'JSON representation');
    }

    public function it_is_readable()
    {
        $this->label()->shouldBe('label');
        $this->value()->shouldBe('value');
        $this->isValid()->shouldBe(true);
        $this->isOwner()->shouldBe(false);
    }
}
