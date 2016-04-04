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

    // @codingStandardsIgnoreStart

    /**
     * Open a stream.
     *
     * @param string $path The path for the stream.
     * @return bool Whether the stream could be opened.
     */
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
    }

    public function stream_read($length)
    {
        return $this->getResponse()->read($length);
    }

    public function stream_eof()
    {
        return $this->getResponse()->eof();
    }

    // @codingStandardsIgnoreEnd

    //endregion

    //region Getters and setters

    /**
     * Get the current stream context.
     *
     * @return resource The stream context.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get the emulator.
     *
     * @return Emulator The current emulator.
     */
    public function getEmulator()
    {
        return $this->emulator;
    }

    /**
     * Set the emulator to use.
     *
     * @param Emulator $emulator The emulator to use.
     * @return $this
     */
    public function setEmulator(Emulator $emulator)
    {
        $this->emulator = $emulator;

        return $this;
    }

    /**
     * Get the emulation to run.
     *
     * @return callable
     */
    public function getEmulation()
    {
        return static::$emulation;
    }

    /**
     * Get the response.
     *
     * @return StreamInterface The response as stream.
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the response for this stream.
     *
     * @param StreamInterface $stream The response as stream.
     * @return $this
     */
    public function setResponse(StreamInterface $stream)
    {
        $this->response = $stream;

        return $this;
    }

    //endregion

    /**
     * Calls the emulation.
     *
     * @param StreamInterface $stream The request stream.
     * @return StreamInterface The response stream.
     */
    public function callEmulation(StreamInterface $stream)
    {
        return call_user_func($this->getEmulation(), $stream);
    }

    /**
     * Get an emulator instance.
     *
     * @param string $scheme The scheme to get an emulator for.
     * @param string $path The path of the stream.
     * @param resource $context The stream resource.
     * @return \CvoTechnologies\StreamEmulation\Emulator\Emulator The emulator instance.
     */
    public static function getEmulatorInstance($scheme, $path, $context)
    {
        if (!isset(static::$emulators[$scheme])) {
            throw new \InvalidArgumentException('No emulator found for scheme \'' . $scheme . '\'');
        }
        $emulator = static::$emulators[$scheme];

        return new $emulator($path, $context);
    }

    /**
     * Set the emulation to use.
     *
     * @param string|callable|object $emulation The emulation to use.
     * @param callable|null $assertionCallable The assertion call to use.
     * @return void
     */
    public static function emulate($emulation, callable $assertionCallable = null)
    {
        if ((is_string($emulation)) && (class_exists($emulation))) {
            $emulation = new $emulation($assertionCallable);
        }

        static::$emulation = $emulation;
    }

    /**
     * Registers an emulator class.
     *
     * @param string $scheme The method this emulator has to be registered to.
     * @param string $class The class name of the emulator.
     * @return void
     */
    public static function registerEmulator($scheme, $class)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException('Class \'' . $class . '\' does not exist');
        }

        static::$emulators[$scheme] = $class;
    }

    /**
     * Register a wrapper for the specified protocol.
     *
     * @param string $protocol The protocol to register a wrapper for.
     * @param bool $emulation Whether to suffix it with emulation.
     * @return void
     */
    public static function registerWrapper($protocol, $emulation = true)
    {
        if ($emulation) {
            $protocol .= '-emulation';
        }

        stream_wrapper_register($protocol, 'CvoTechnologies\StreamEmulation\StreamWrapper');
    }

    /**
     * Unregister a wrapper for the specified protocol.
     *
     * @param string $protocol The protocol to unregister a wrapper for.
     * @param bool $emulation Whether to suffix it with emulation.
     * @return void
     */
    public static function unregisterWrapper($protocol, $emulation = true)
    {
        if ($emulation) {
            $protocol .= '-emulation';
        }

        stream_wrapper_unregister($protocol);
    }

    /**
     * Override a internal stream wrapper.
     *
     * @param string $protocol The protocol to override.
     * @return void
     */
    public static function overrideWrapper($protocol)
    {
        stream_wrapper_unregister($protocol);

        static::registerWrapper($protocol, false);
    }

    /**
     * Restore an internal stream wrapper.
     *
     * @param string $protocol The protocol of the stream wrapper to restore.
     * @return void
     */
    public static function restoreWrapper($protocol)
    {
        stream_wrapper_restore($protocol);
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
        return $this->getEmulator()->offsetExists($offset);
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
        return $this->getEmulator()->offsetGet($offset);
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
        $this->getEmulator()->offsetSet($offset, $value);
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
        $this->getEmulator()->offsetUnset($offset);
    }
}
