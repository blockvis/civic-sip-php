<?php

namespace Blockvis\Civic\Sip;

class UserDataItem
{
    /**
     * Descriptive value identifier.
     *
     * @var string
     */
    private $label;

    /**
     * Item value of requested user data.
     *
     * @var mixed
     */
    private $value;

    /**
     * Indicates whether or not the data is still considered valid on the blockchain.
     *
     * @var bool
     */
    private $isValid;

    /**
     * Civic SIP service challenges the user during scope request approval to ensure
     * the user is in control of the private key originally used in the issuance of the data attestation.
     *
     * @var bool
     */
    private $isOwner;

    /**
     * UserDataItem constructor.
     * @param string $label
     * @param mixed $value
     * @param bool $isValid
     * @param bool $isOwner
     */
    public function __construct(string $label, $value, bool $isValid, bool $isOwner)
    {
        $this->label = $label;
        $this->value = $value;
        $this->isValid = $isValid;
        $this->isOwner = $isOwner;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return bool
     */
    public function isOwner(): bool
    {
        return $this->isOwner;
    }

}
