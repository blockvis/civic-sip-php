<?php

namespace Blockvis\Civic\Sip;

class UserData
{
    /**
     * @var string
     */
    private $userId;

    /**
     * @var UserDataItem[]
     */
    private $items = [];

    /**
     * UserData constructor.
     * @param string $userId
     * @param UserDataItem[] $items
     */
    public function __construct(string $userId, array $items = [])
    {
        $this->userId = $userId;
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function userId(): string
    {
        return $this->userId;
    }

    /**
     * @return UserDataItem[]
     */
    public function items(): array
    {
        return $this->items;
    }
}
