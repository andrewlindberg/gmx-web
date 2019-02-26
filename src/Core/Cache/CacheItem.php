<?php

namespace GameX\Core\Cache;

use \Stash\Interfaces\ItemInterface;

abstract class CacheItem
{

    /**
     * @param ItemInterface $item
     * @param mixed|null $element
     * @return mixed
     */
    public function get(ItemInterface $item, $element = null)
    {
        $data = $item->get();
        if ($item->isMiss()) {
            $item->lock();
            $data = $this->getData($element);
            $item->set($data);
            $item->save();
        }
        return $data;
    }

    /**
     * @param ItemInterface $item
     * @return bool
     */
    public function clear(ItemInterface $item)
    {
        return $item->clear();
    }

    /**
     * @param $key $key
     * @param mixed|null $element
     * @return mixed
     */
    public function getKey($key, $element = null)
    {
        return $element !== null ? $key . '_' . (string)$element : $key;
    }

    /**
     * @param mixed|null $element
     * @return mixed
     */
    abstract protected function getData($element);
}