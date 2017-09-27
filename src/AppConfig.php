<?php

namespace Blockvis\Civic\Sip;

class AppConfig
{
    /**
     * Application environment ('prod' by default).
     *
     * @var string
     */
    private $env;

    /**
     * Application ID.
     *
     * @var string
     */
    private $id;

    /**
     * Application Private Signing Key.
     *
     * @var string
     */
    private $privateKey;

    /**
     * Application Secret.
     *
     * @var string
     */
    private $secret;

    /**
     * AppConfig constructor.
     *
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
     * Returns the application environment.
     *
     * @return string
     */
    public function env(): string
    {
        return $this->env;
    }

    /**
     * Returns the application id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the application private signing key.
     *
     * @return string
     */
    public function privateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * Returns the application secret.
     *
     * @return string
     */
    public function secret(): string
    {
        return $this->secret;
    }
}
