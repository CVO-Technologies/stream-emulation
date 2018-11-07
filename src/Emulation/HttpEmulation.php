<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class HttpEmulation extends Emulation implements MessageAwareEmulationInterface
{
    use MessageAwareEmulationTrait;

    protected $_headers = [];
    protected $_content = '';
    protected $assertionCallback;

    /**
     * HttpEmulation constructor.
     *
     * @param callable|null $assertionCallback The assertion callback to use.
     */
    public function __construct(callable $assertionCallback = null)
    {
        if ($assertionCallback) {
            $this->setAssertionCallback($assertionCallback);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(StreamInterface $stream)
    {
        $request = \GuzzleHttp\Psr7\parse_request($stream->getContents());
        $response = $this->run($request);

        if ($this->getAssertionCallback()) {
            call_user_func($this->getAssertionCallback(), $request);
        }

        return \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\Psr7\str($response));
    }

    /**
     * Run the HTTP emulation.
     *
     * @param \Psr\Http\Message\RequestInterface $request The request object/
     * @return \Psr\Http\Message\ResponseInterface The response object.
     */
    abstract protected function run(RequestInterface $request);

    /**
     * Set the callback used to check for assertions.
     *
     * @return callback The callback used to check for assertions.
     */
    public function getAssertionCallback()
    {
        return $this->assertionCallback;
    }

    /**
     * Get the callback used to check for assertions.
     *
     * @param callable $assertionCallback The callback used to check for assertions.
     * @return $this
     */
    public function setAssertionCallback($assertionCallback)
    {
        $this->assertionCallback = $assertionCallback;

        return $this;
    }

    /**
     * Get an emulator using an callable.
     *
     * @param callable $callable The callable to use in the emulation.
     * @param callable $assertedCallback The callback used to check for assertions.
     * @return HttpEmulation A HTTP emulation instance.
     */
    public static function fromCallable(callable $callable, callable $assertedCallback = null)
    {
        return new HttpCallableEmulation($callable, $assertedCallback);
    }
}
