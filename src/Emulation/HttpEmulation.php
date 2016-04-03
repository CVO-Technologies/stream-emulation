<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use function GuzzleHttp\Psr7\parse_request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class HttpEmulation extends Emulation
{
    protected $_headers = [];
    protected $_content = '';

    public function __invoke(StreamInterface $stream)
    {
        $response = $this->_run(parse_request($stream->getContents()));

        $content = 'HTTP/' . $response->getProtocolVersion() . ' ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . "\r\n";
        foreach ($response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                $content .= $header . ': ' . $value . "\r\n";
            }
        }
        $content .= "\r\n";
        $content .= $response->getBody()->getContents();

        return \GuzzleHttp\Psr7\stream_for($content);
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    abstract protected function _run(RequestInterface $request);

    /**
     * Get an emulator using an callable.
     *
     * @param callable $callable
     * @return HttpEmulation
     */
    public static function fromCallable(callable $callable)
    {
        return new HttpCallableEmulation($callable);
    }
}
