<?php

namespace CvoTechnologies\StreamEmulation\Emulation;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class SequentialEmulation extends Emulation
{
    /**
     * @var callable[]
     */
    protected $emulations;
    /**
     * @var int
     */
    protected $invocation = 0;
    /**
     * @var Emulation
     */
    protected $overflowEmulation;

    public function __construct(array $emulations, Emulation $overflowEmulation = null)
    {
        $this->emulations = $emulations;
        $this->overflowEmulation = $overflowEmulation;
    }

    /**
     * Run the emulation.
     *
     * @param StreamInterface $stream The request as stream.
     * @return StreamInterface The response as stream.
     */
    public function __invoke(StreamInterface $stream)
    {
        if (!isset($this->emulations[$this->invocation])) {
            if (!$this->overflowEmulation) {
                throw new RuntimeException('No emulation available for invocation #' . $this->invocation);
            }

            $this->emulations[$this->invocation] = $this->overflowEmulation;
        }

        $invocation = $this->invocation++;

        return call_user_func($this->emulations[$invocation], $stream);
    }
}
