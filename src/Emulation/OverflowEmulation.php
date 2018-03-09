<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class OverflowEmulation extends HttpEmulation
{
    protected $exception;

    protected function run(RequestInterface $request)
    {
        $this->exception = new Exception(
            'Unexpected emulation call to ' . (string) $request->getUri(),
            0,
            $this->exception
        );

        return new Response(503, [], $this->exception->getMessage());
    }

    public function assert()
    {
        if (!$this->exception) {
            return;
        }

        throw $this->exception;
    }
}
