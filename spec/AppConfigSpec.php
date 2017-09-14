<?php

namespace spec\Blockvis\Civic\Sip;

use Blockvis\Civic\Sip\AppConfig;
use PhpSpec\ObjectBehavior;

class AppConfigSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', 'secret', 'pkey', 'env');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AppConfig::class);
    }

    public function it_is_readable()
    {
        $this->id()->shouldBe('id');
        $this->secret()->shouldBe('secret');
        $this->privateKey()->shouldBe('pkey');
        $this->env()->shouldBe('env');
    }

}
