<?php

namespace CvoTechnologies\StreamEmulation;

use ArrayAccess;
use CvoTechnologies\StreamEmulation\Emulator\Emulator;
use Psr\Http\Message\StreamInterface;

class StreamWrapper implements ArrayAccess
{
    /**
     * @var resource
     */
    public $context;

    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $response;

    /**
     * @var \CvoTechnologies\StreamEmulation\Emulator\Emulator
     */
    protected $emulator;

    protected static $emulators = [
        'http' => 'CvoTechnologies\StreamEmulation\Emulator\HttpEmulator',
        'https' => 'CvoTechnologies\StreamEmulation\Emulator\HttpsEmulator',
    ];

    protected static $emulation;

    //region Stream wrapper methods
    public function stream_open($path)
    {
        $scheme = parse_url($path, PHP_URL_SCHEME);
        if (substr($scheme, -10) === '-emulation') {
            $scheme = substr($scheme, 0, -10);
        }

        $emulator = static::getEmulatorInstance($scheme, $path, $this->getContext());
        if (!$emulator) {
            return false;
        }

        $this->setEmulator($emulator);

        $this->getEmulator()->setResponseStream($this->callEmulation($this->getEmulator()->getIncomingStream()));
        $this->setResponse($this->getEmulator()->getOutgoingStream());

        return true;
    }

    public function stream_stat()
    {
        return;
    }

    public function stream_read($length)
    {
        return $this->getResponse()->read($length);
    }

    public function stream_eof()
    {
        return $this->getResponse()->eof();
    }
    //endregion

    //region Getters and setters
    public function getContext()
    {
        return $this->context;
    }

    public function getEmulator()
    {
        return $this->emulator;
    }

    public function setEmulator(Emulator $emulator)
    {
        $this->emulator = $emulator;

        return $this;
    }

    public function getEmulation()
    {
        return static::$emulation;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(StreamInterface $stream)
    {
        return $this->response = $stream;
    }
    //endregion

    public function callEmulation(StreamInterface $stream)
    {
        return call_user_func($this->getEmulation(), $stream);
    }

    public static function getEmulatorInstance($scheme, $path, $context)
    {
        if (!isset(static::$emulators[$scheme])) {
            throw new \InvalidArgumentException('No emulator found for scheme \'' . $scheme . '\'');
        }
        $streamImplementationClass = static::$emulators[$scheme];

        return new $streamImplementationClass($path, $context);
    }

    public static function emulate($emulation)
    {
        if ((is_string($emulation)) && (class_exists($emulation))) {
            $emulation = new $emulation;
        }

        static::$emulation = $emulation;
    }

    public static function registerEmulator($scheme, $class)
    {
        static::$emulators[$scheme] = $class;
    }

    public static function registerWrapper($protocol, $emulation = true)
    {
        if ($emulation) {
            $protocol .= '-emulation';
        }

        stream_wrapper_register($protocol, 'CvoTechnologies\StreamEmulation\StreamWrapper');
    }

    public static function unregisterWrapper($protocol, $emulation = true)
    {
        if ($emulation) {
            $protocol .= '-emulation';
        }

        stream_wrapper_unregister($protocol);
    }

    public static function overrideWrapper($protocol)
    {
        stream_wrapper_unregister($protocol);

        static::registerWrapper($protocol, false);
    }

    public static function restoreWrapper($protocol)
    {
        stream_wrapper_restore($protocol);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->getEmulator()->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getEmulator()->offsetGet($offset);
    }

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
    public function offsetSet($offset, $value)
    {
        $this->getEmulator()->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->getEmulator()->offsetUnset($offset);
    }
}
