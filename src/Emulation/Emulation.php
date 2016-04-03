<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\StreamInterface;

abstract class Emulation
{
    /**
     * Run the emulation.
     *
     * @param StreamInterface $stream The request as stream.
     * @return StreamInterface The response as stream.
     */
    abstract public function __invoke(StreamInterface $stream);
}
