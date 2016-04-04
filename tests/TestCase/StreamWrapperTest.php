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

    public function testHttpEmulationEmulate()
    {
        StreamWrapper::registerWrapper('http');
        $invocations = [];
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) use (&$invocations) {
            $invocations[] = $request;

            return new Response(200, [], 'test123');
        }));

        $this->assertEquals('test123', file_get_contents('http-emulation://example.com'));
        $this->assertCount(1, $invocations);
        $this->assertEquals('/', $invocations[0]->getUri()->getPath());

        StreamWrapper::unregisterWrapper('http');
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

    public function testArrayAccess()
    {
        StreamWrapper::emulate(HttpEmulation::fromCallable(function (RequestInterface $request) use (&$invocations) {
            $invocations[] = $request;

            return new Response(200, [], 'test123');
        }));

        $context = fopen('https://example.com', 'r');

        $httpEmulator = stream_get_meta_data($context)['wrapper_data'];

        $this->assertTrue(isset($httpEmulator['headers']));
        $this->assertInternalType('array', $httpEmulator['headers']);
        $this->assertCount(1, $httpEmulator['headers']);
        $this->assertEquals('HTTP/1.1 200 OK', $httpEmulator['headers'][0]);

        unset($httpEmulator['headers']);
        $this->assertTrue(isset($httpEmulator['headers']));

        $httpEmulator['headers'] = [];
        $this->assertCount(1, $httpEmulator['headers']);

        fclose($context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No emulator found for scheme 'test'
     */
    public function testNonDefinedScheme()
    {
        StreamWrapper::registerWrapper('test', false);

        $context = fopen('test://example.com', 'r');
        fclose($context);
    }

    protected function tearDown()
    {
        StreamWrapper::restoreWrapper('https');
    }
}
