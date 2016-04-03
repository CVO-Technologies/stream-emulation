<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\RequestInterface;

class HttpCallableEmulation extends HttpEmulation
{
    protected $callable;

    /**
     * HttpCallableEmulation constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }


    /**
     * {@inheritDoc}
     */
    protected function _run(RequestInterface $request)
    {
        return call_user_func($this->callable, $request);
    }
}
