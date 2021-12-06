<?php

namespace Belltastic;

abstract class BelltasticObject implements \ArrayAccess, \JsonSerializable
{
    public $attributes = [];

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize()
    {
        return json_encode($this->attributes);
    }

    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getHidden()
    {
        return [];
    }

    public function __get($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
}
