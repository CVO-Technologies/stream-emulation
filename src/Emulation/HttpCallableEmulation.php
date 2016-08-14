<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\RequestInterface;

class HttpCallableEmulation extends HttpEmulation
{
    protected $callable;

    /**
     * Construct the callable based HTTP emulation.
     *
     * @param callable $callable The callable to use.
     * @param callable|null $assertedCallback The callback used to check for assertions.
     * @internal
     */
    public function __construct(callable $callable, callable $assertedCallback = null)
    {
        parent::__construct($assertedCallback);

        $this->callable = $callable;
    }

    /**
     * {@inheritDoc}
     */
    protected function run(RequestInterface $request)
    {
        return call_user_func($this->callable, $request);
    }
}
