<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

interface MessageAwareEmulationInterface
{
    public function getMessage();

    public function setMessage(string $message);
}
