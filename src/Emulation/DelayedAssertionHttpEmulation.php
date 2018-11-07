<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class DelayedAssertionHttpEmulation extends HttpEmulation
{
    /**
     * @var HttpEmulation
     */
    protected $emulation;
    /**
     * @var bool
     */
    protected $invoked = false;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(HttpEmulation $emulation)
    {
        parent::__construct($emulation->getAssertionCallback());

        $this->emulation = $emulation;
    }

    public function __invoke(StreamInterface $stream)
    {
        $this->invoked = true;

        $request = \GuzzleHttp\Psr7\parse_request($stream->getContents());
        $response = $this->run($request);

        $this->request = $request;

        return \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\Psr7\str($response));
    }

    public function isInvoked(): bool
    {
        return $this->invoked;
    }

    public function runAssertionCallback()
    {
        if (!$this->getAssertionCallback() || !$this->request) {
            return;
        }

        call_user_func($this->getAssertionCallback(), $this->request);
    }

    public function getMessage()
    {
        return $this->emulation->message;
    }

    public function setMessage(string $message)
    {
        throw new RuntimeException('Changing the message of a delayed assertion http emulation is not possible');
    }

    protected function run(RequestInterface $request)
    {
        return $this->emulation->run($request);
    }
}
