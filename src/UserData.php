<?php

namespace Blockvis\Civic\Sip;

use JsonSerializable;

class UserData implements JsonSerializable
{
    /**
     * @var UserDataItem[]
     */
    private $items = [];

    /**
     * @var string
     */
    private $userId;

    /**
     * UserData constructor.
     *
     * @param string $userId
     * @param array $data
     */
    public function __construct($userId, array $data = [])
    {
        $this->userId = (string)$userId;
        $this->items = $this->createDataItems($data);
    }

    /**
     * Returns user data item by its label.
     *
     * @param string $label
     * @return UserDataItem|null
     */
    public function getByLabel($label)
    {
        return isset($this->items[$label]) ? $this->items[$label] : null;
    }

    /**
     * Returns all the user data items.
     *
     * @return UserDataItem[]
     */
    public function items()
    {
        return array_values($this->items);
    }

    /**
     * @return array|UserDataItem[]
     */
    public function jsonSerialize()
    {
        return $this->items();
    }

    /**
     * Returns the user id.
     *
     * @return string
     */
    public function userId()
    {
        return $this->userId;
    }

    /**
     * Creates data item object from array.
     *
     * @param array $data
     * @return array
     */
    private function createDataItems(array $data)
    {
        $items = [];
        foreach ($data as $item) {
            $items[$item['label']] = new UserDataItem(
                $item['label'],
                $item['value'],
                $item['isValid'],
                $item['isOwner']
            );
        }

        return $items;
    }

}
