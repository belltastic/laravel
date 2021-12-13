<?php

namespace Belltastic;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

abstract class BelltasticObject implements \ArrayAccess, \JsonSerializable
{
    public $attributes = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'read_at',
        'seen_at',
    ];

    public function __construct($attributes = [])
    {
        $this->forceFill($attributes);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->attributes[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function jsonSerialize()
    {
        return json_encode($this->toFlatArray());
    }

    public function toFlatArray(): array
    {
        $attributes = $this->attributes;

        foreach ($attributes as $key => $value) {
            if ($value instanceof Carbon) {
                $attributes[$key] = $value->toIso8601String();
            }
        }

        return $attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson()
    {
        return $this->jsonSerialize();
    }

    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if ($this->canBeFilled($key)) {
                $this->setAttribute($key, $value);
            }
        }
    }

    public function forceFill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    public function canBeFilled($key): bool
    {
        if (property_exists($this, 'fillable') && is_array($this->fillable)) {
            return in_array($key, $this->fillable);
        }

        return true;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->dates)) {
            $this->attributes[$key] = $value ? Date::parse($value) : null;
        } else {
            $this->attributes[$key] = $value;
        }
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
        $this->setAttribute($name, $value);
    }
}
