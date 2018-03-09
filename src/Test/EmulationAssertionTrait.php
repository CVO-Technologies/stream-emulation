<?php

namespace CvoTechnologies\StreamEmulation\Test;

use CvoTechnologies\StreamEmulation\Emulation\DelayedAssertionHttpEmulation;
use CvoTechnologies\StreamEmulation\Emulation\Emulation;
use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\Emulation\OverflowEmulation;
use CvoTechnologies\StreamEmulation\Emulation\SequentialEmulation;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_ExpectationFailedException;
use Psr\Http\Message\RequestInterface;

/**
 * @mixin TestCase
 */
trait EmulationAssertionTrait
{
    /**
     * @var string[]
     */
    protected $emulationProtocols = [];
    /**
     * @var Emulation[]
     */
    protected $expectedEmulations = [];
    /**
     * @var Emulation[]
     */
    protected $originalEmulations = [];
    /**
     * @var OverflowEmulation
     */
    protected $overFlowEmulation = null;

    public function expectEmulation(int $invocation, Emulation $emulation)
    {
        if ($emulation instanceof HttpEmulation) {
            $emulation = new DelayedAssertionHttpEmulation($emulation);
        }

        $this->originalEmulations[$invocation] = $emulation;
    }

    public function expectEmulations(array $protocols, Emulation ...$emulations)
    {
        $this->resetEmulation();

        foreach ($emulations as $index => $emulation) {
            $this->expectEmulation($index, $emulation);
        }

        $this->setupEmulation($protocols);
    }

    public function setupEmulation(array $protocols)
    {
        $this->emulationProtocols = $protocols;
        $this->overFlowEmulation = new OverflowEmulation();

        $this->rewindEmulation();
    }

    public function resetEmulation()
    {
        $this->emulationProtocols = [];
        $this->expectedEmulations = [];
        $this->originalEmulations = [];
        $this->overFlowEmulation = [];
    }

    public function rewindEmulation()
    {
        foreach ($this->emulationProtocols as $protocol) {
            StreamWrapper::overrideWrapper($protocol);
        }

        foreach ($this->originalEmulations as $index => $emulation) {
            $this->expectedEmulations[$index] = clone $emulation;
        }

        StreamWrapper::emulate(new SequentialEmulation(
            $this->expectedEmulations,
            $this->overFlowEmulation
        ));
    }

    public function assertEmulations()
    {
        StreamWrapper::emulate(null);
        foreach ($this->emulationProtocols as $protocol) {
            StreamWrapper::restoreWrapper($protocol);
        }

        foreach ($this->expectedEmulations as $index => $emulation) {
            if (!$emulation instanceof DelayedAssertionHttpEmulation) {
                continue;
            }

            $messageAppend = '';
            if ($emulation->getMessage()) {
                $messageAppend .= ': ' . $emulation->getMessage();
            }

            $this->assertTrue($emulation->isInvoked(), 'Expected emulation #' . $index . ' to have been invoked' . $messageAppend);

            try {
                $emulation->runAssertionCallback();
            } catch (PHPUnit_Framework_ExpectationFailedException $expectationFailedException) {
                throw new PHPUnit_Framework_ExpectationFailedException(
                    'Emulation #' . $index . ' failed' . $messageAppend,
                    $expectationFailedException->getComparisonFailure(),
                    $expectationFailedException
                );
            } catch (ExpectationFailedException $expectationFailedException) {
                throw new ExpectationFailedException(
                    'Emulation #' . $index . ' failed' . $messageAppend,
                    $expectationFailedException->getComparisonFailure(),
                    $expectationFailedException
                );
            }
        }
        $this->overFlowEmulation->assert();

        $this->rewindEmulation();
    }
}
