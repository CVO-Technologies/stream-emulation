<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

trait MessageAwareEmulationTrait
{
    protected $message;

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}
