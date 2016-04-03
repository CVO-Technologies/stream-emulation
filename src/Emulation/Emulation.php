<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\StreamInterface;

abstract class Emulation
{
    abstract function __invoke(StreamInterface $stream);
}
