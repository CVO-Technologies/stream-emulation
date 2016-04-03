<?php

namespace CvoTechnologies\StreamEmulation\Emulator;

use Psr\Http\Message\StreamInterface;

abstract class Emulator implements \ArrayAccess
{
    protected $path;
    protected $context;
    protected $responseStream;

    /**
     * Construct an emulator.
     *
     * @param string $path The path to the stream.
     * @param resource $context The current stream resource.
     */
    public function __construct($path, $context)
    {
        $this->setPath($path);
        $this->setContext($context);
    }

    /**
     * Return a stream with the incoming data.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    abstract public function getIncomingStream();

    /**
     * Return a stream with the outgoing data.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    abstract public function getOutgoingStream();

    /**
     * Get the stream path.
     *
     * @return string The stream path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the stream path.
     *
     * @param string $path The path to the stream
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }


    /**
     * Get the current stream context.
     *
     * @return resource The current stream context.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the current stream context.
     *
     * @param resource $context The stream context to set.
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Set the response stream.
     *
     * @param \Psr\Http\Message\StreamInterface $response The response stream.
     * @return $this
     */
    public function setResponseStream(StreamInterface $response)
    {
        $this->responseStream = $response;

        return $this;
    }

    /**
     * Get the response stream.
     *
     * @return \Psr\Http\Message\StreamInterface The response stream.
     */
    public function getResponseStream()
    {
        return $this->responseStream;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    abstract public function offsetExists($offset);

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    abstract public function offsetGet($offset);

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    abstract public function offsetSet($offset, $value);

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    abstract public function offsetUnset($offset);
}
