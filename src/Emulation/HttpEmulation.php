<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class HttpEmulation extends Emulation
{
    protected $_headers = [];
    protected $_content = '';

    /**
     * {@inheritDoc}
     */
    public function __invoke(StreamInterface $stream)
    {
        $response = $this->_run(\GuzzleHttp\Psr7\parse_request($stream->getContents()));

        return \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\Psr7\str($response));
    }

    /**
     * Run the HTTP emulation.
     *
     * @param \Psr\Http\Message\RequestInterface $request The request object/
     * @return \Psr\Http\Message\ResponseInterface The response object.
     */
    abstract protected function _run(RequestInterface $request);

    /**
     * Get an emulator using an callable.
     *
     * @param callable $callable The callable to use in the emulation.
     * @return HttpEmulation A HTTP emulation instance.
     */
    public static function fromCallable(callable $callable)
    {
        return new HttpCallableEmulation($callable);
    }
}
