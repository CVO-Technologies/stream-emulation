<?php

namespace CvoTechnologies\StreamEmulation\Test\TestCase;

use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use CvoTechnologies\StreamEmulation\Emulator\HttpEmulator;
use CvoTechnologies\StreamEmulation\StreamWrapper;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\StreamWrapper as GuzzleStreamWrapper;
use Psr\Http\Message\RequestInterface;

class StreamWrapperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        StreamWrapper::overrideWrapper('http');
        StreamWrapper::overrideWrapper('https');
    }

    public function testHttpEmulate()
    {
        $invocations = [];
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) use (&$invocations) {
            $invocations[] = $request;

            return new Response(200, [], 'test123');
        }));

        $this->assertEquals('test123', file_get_contents('http://example.com'));
        $this->assertCount(1, $invocations);
        $this->assertEquals('/', $invocations[0]->getUri()->getPath());
    }

    public function testHttpsEmulate()
    {
        $invocations = [];
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) use (&$invocations) {
            $invocations[] = $request;

            return new Response(200, [], 'test123');
        }));

        $this->assertEquals('test123', file_get_contents('https://example.com'));
        $this->assertCount(1, $invocations);
        $this->assertEquals('/', $invocations[0]->getUri()->getPath());
    }

    public function testRegisterWrapper()
    {
        StreamWrapper::registerWrapper('http');
        $this->assertContains('http-emulation', stream_get_wrappers());

        StreamWrapper::unregisterWrapper('http');
        $this->assertNotContains('http-emulation', stream_get_wrappers());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class 'NonExisting' does not exist
     */
    public function testRegisteringNonExistingEmulator()
    {
        StreamWrapper::registerEmulator('none', 'NonExisting');
    }

    public function testGetContext()
    {
        $stream = GuzzleStreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for('test123'));
        stream_context_set_option($stream, 'http', 'content', 'test123');

        $streamWrapper = new StreamWrapper();
        $streamWrapper->context = $stream;

        $this->assertSame($stream, $streamWrapper->getContext());
    }

    public function testGetEmulator()
    {
        $stream = GuzzleStreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for('test123'));
        stream_context_set_option($stream, 'http', 'content', 'test123');

        $streamWrapper = new StreamWrapper();
        $streamWrapper->context = $stream;
        $streamWrapper->stream_open('http://example.com');

        $this->assertInstanceOf('CvoTechnologies\StreamEmulation\Emulator\HttpEmulator', $streamWrapper->getEmulator());
    }

    public function testSetEmulator()
    {
        $stream = GuzzleStreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for('test123'));
        stream_context_set_option($stream, 'http', 'content', 'test123');

        $streamWrapper = new StreamWrapper();
        $streamWrapper->context = $stream;

        $emulator = new HttpEmulator('http://example.com', $streamWrapper->getContext());
        $this->assertSame($streamWrapper, $streamWrapper->setEmulator($emulator));

        $this->assertSame($emulator, $streamWrapper->getEmulator());
    }

    protected function tearDown()
    {
        StreamWrapper::restoreWrapper('https');
    }
}
