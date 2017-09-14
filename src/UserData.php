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
     * @param array $data
     */
    public function __construct(string $userId, array $data = [])
    {
        $this->userId = $userId;
        $this->items = $this->createDataItems($data);
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

    /**
     * @param array $data
     * @return array
     */
    private function createDataItems(array $data): array
    {
        $items = [];
        foreach ($data as $item) {
            $items[] = new UserDataItem(
                $item['label'],
                $item['value'],
                $item['isValid'],
                $item['isOwner']
            );
        }

        return $items;
    }
}
