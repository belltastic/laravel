<?php

namespace Belltastic;

abstract class Query
{
    /**
     * Class of the destination model
     *
     * @var string
     */
    protected $model;

    /**
     * List of function arguments to pass-through to the destination class
     *
     * @var array
     */
    protected $passthroughArguments = [];

    public function __construct()
    {
        $this->passthroughArguments = func_get_args();
    }

    public function __call($name, $arguments)
    {
        return call_user_func(
            [$this->model, $name],
            ...array_merge($this->passthroughArguments, $arguments)
        );
    }
}
