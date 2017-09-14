<?php

namespace Blockvis\Civic\Sip;

class AppConfig
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $env;

    /**
     * AppConfig constructor.
     * @param string $id
     * @param string $secret
     * @param string $privateKey
     * @param string $env
     */
    public function __construct(string $id, string $secret, string $privateKey, string $env = 'prod')
    {
        $this->id = $id;
        $this->secret = $secret;
        $this->privateKey = $privateKey;
        $this->env = strtolower($env);
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function secret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function privateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function env(): string
    {
        return $this->env;
    }
}
