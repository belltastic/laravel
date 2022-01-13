<?php

namespace Belltastic\Exceptions;

use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;

class Exception extends \Exception
{
    /** @var string */
    public $url;

    /** @var string */
    public $token;

    public function __construct($message = "", RequestInterface $request = null)
    {
        $this->url = optional($request)->getUri();
        $token = Str::replace('Bearer ', '', optional($request)->getHeaderLine('authorization'));
        $this->token = Str::limit($token, 10, '****');

        if (! empty($this->getUrl())) {
            $message .= "\nRequest URL: " . $this->getUrl();
            $message .= ' (with API token ' . $this->getToken() . ')';
        }

        parent::__construct($message);
    }

    public function getUrl(): string
    {
        return $this->url ?: '';
    }

    public function getToken(): string
    {
        return $this->token ?: '<empty>';
    }
}
