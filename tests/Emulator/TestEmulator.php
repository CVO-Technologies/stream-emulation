<?php

namespace CvoTechnologies\StreamEmulation\Test\Emulator;

use CvoTechnologies\StreamEmulation\Emulator\Emulator;

class TestEmulator extends Emulator
{
    /**
     * Return a stream with the incoming data.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getIncomingStream()
    {
        return \GuzzleHttp\Psr7\stream_for('test!');
    }

    /**
     * Return a stream with the outgoing data.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getOutgoingStream()
    {
        return \GuzzleHttp\Psr7\stream_for('test!');
    }

    /**
     * Whether a offset exists.
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
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * Offset to retrieve.
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * Offset to set.
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
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Offset to unset.
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
    }
}
