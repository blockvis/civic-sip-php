<?php

namespace Blockvis\Civic\Sip;

use JsonSerializable;

class UserDataItem implements JsonSerializable
{
    /**
     * Civic SIP service challenges the user during scope request approval to ensure
     * the user is in control of the private key originally used in the issuance of the data attestation.
     *
     * @var bool
     */
    private $isOwner;

    /**
     * Indicates whether or not the data is still considered valid on the blockchain.
     *
     * @var bool
     */
    private $isValid;

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
     * UserDataItem constructor.
     *
     * @param string $label
     * @param mixed $value
     * @param bool $isValid
     * @param bool $isOwner
     */
    public function __construct($label, $value, $isValid, $isOwner)
    {
        $this->label = (string)$label;
        $this->value = (string)$value;
        $this->isValid = (bool)$isValid;
        $this->isOwner = (bool)$isOwner;
    }

    /**
     * Returns true if user is in control of the private key
     * originally used in the issuance of the data attestation,
     * false otherwise.
     *
     * @return bool
     */
    public function isOwner()
    {
        return $this->isOwner;
    }

    /**
     * Returns true if the item is still considered valid on the blockchain, false otherwise.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
            'isValid' => $this->isValid,
            'isOwner' => $this->isOwner,
        ];
    }

    /**
     * Returns the item label.
     *
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * Returns the item value.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

}
