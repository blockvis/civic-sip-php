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
    public function __construct(string $userId, array $data = [])
    {
        $this->userId = $userId;
        $this->items = $this->createDataItems($data);
    }

    /**
     * Returns user data item by its label.
     *
     * @param string $label
     * @return UserDataItem|null
     */
    public function getByLabel(string $label): ?UserDataItem
    {
        return $this->items[$label] ?? null;
    }

    /**
     * Returns all the user data items.
     *
     * @return UserDataItem[]
     */
    public function items(): array
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
    public function userId(): string
    {
        return $this->userId;
    }

    /**
     * Creates data item object from array.
     *
     * @param array $data
     * @return array
     */
    private function createDataItems(array $data): array
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
