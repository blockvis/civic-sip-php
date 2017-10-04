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
    public function __construct($id, $secret, $privateKey, $env = 'prod')
    {
        $this->id = (string)$id;
        $this->secret = (string)$secret;
        $this->privateKey = (string)$privateKey;
        $this->env = strtolower($env);
    }

    /**
     * Returns the application environment.
     *
     * @return string
     */
    public function env()
    {
        return $this->env;
    }

    /**
     * Returns the application id.
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Returns the application private signing key.
     *
     * @return string
     */
    public function privateKey()
    {
        return $this->privateKey;
    }

    /**
     * Returns the application secret.
     *
     * @return string
     */
    public function secret()
    {
        return $this->secret;
    }
}
