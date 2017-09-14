<?php

namespace spec\Blockvis\Civic\Sip;

use Blockvis\Civic\Sip\Client;
use Blockvis\Civic\Sip\AppConfig;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;

class ClientSpec extends ObjectBehavior
{
    function let(AppConfig $config, ClientInterface $httpClient)
    {
        $this->beConstructedWith($config, $httpClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
    }
}
