<?php

namespace CvoTechnologies\StreamEmulation\Test\TestCase\Emulator;

use CvoTechnologies\StreamEmulation\Emulator\HttpEmulator;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\StreamWrapper;

class HttpEmulatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIncomingStreamWithoutContent()
    {
        $stream = StreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for(''));
        $httpEmulator = new HttpEmulator('http://example.com', $stream);

        $lines = explode("\r\n", $httpEmulator->getIncomingStream()->getContents());
        $this->assertEquals('GET / HTTP/1.1', $lines[0]);
        $this->assertEquals('Host: example.com', $lines[1]);
        $this->assertEquals('', $lines[2]);
        $this->assertCount(3, $lines);
    }

    public function testIncomingStreamWithContent()
    {
        $stream = StreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for('test123'));
        stream_context_set_option($stream, 'http', 'content', 'test123');
        $httpEmulator = new HttpEmulator('http://example.com', $stream);

        $lines = explode("\r\n", $httpEmulator->getIncomingStream()->getContents());
        $this->assertEquals('GET / HTTP/1.1', $lines[0]);
        $this->assertEquals('Host: example.com', $lines[1]);
        $this->assertEquals('', $lines[2]);
        $this->assertEquals('test123', $lines[3]);
        $this->assertCount(4, $lines);
    }

    public function testIncomingStreamWithHeaders()
    {
        $stream = StreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for('test123'));
        stream_context_set_option($stream, 'http', 'header', 'X-Test-Header: testvalue');
        $httpEmulator = new HttpEmulator('http://example.com', $stream);

        $lines = explode("\r\n", $httpEmulator->getIncomingStream()->getContents());
        $this->assertEquals('GET / HTTP/1.1', $lines[0]);
        $this->assertEquals('Host: example.com', $lines[1]);
        $this->assertEquals('X-Test-Header: testvalue', $lines[2]);
        $this->assertEquals('', $lines[3]);
        $this->assertCount(4, $lines);
    }

    public function testIncomingStreamWithProtocolVersion()
    {
        $stream = StreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for('test123'));
        stream_context_set_option($stream, 'http', 'protocol_version', '1.0');
        $httpEmulator = new HttpEmulator('http://example.com', $stream);

        $lines = explode("\r\n", $httpEmulator->getIncomingStream()->getContents());
        $this->assertEquals('GET / HTTP/1.0', $lines[0]);
        $this->assertEquals('Host: example.com', $lines[1]);
        $this->assertEquals('', $lines[2]);
        $this->assertCount(3, $lines);
    }

    public function testOutgoingStream()
    {
        $stream = StreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for(''));
        $httpEmulator = new HttpEmulator('http://example.com', $stream);

        $response = 'HTTP/1.1 200 OK' . "\r\n";
        $response .= "\r\n";
        $response .= "test123";

        $httpEmulator->setResponseStream(stream_for($response));

        $response = $httpEmulator->getResponse();
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);

        $this->assertEquals('test123', $httpEmulator->getOutgoingStream()->getContents());
    }

    public function testArrayHeaderAccess()
    {
        $stream = StreamWrapper::getResource(\GuzzleHttp\Psr7\stream_for(''));
        $httpEmulator = new HttpEmulator('http://example.com', $stream);

        $response = 'HTTP/1.1 200 OK' . "\r\n";
        $response .= 'Content-Type: application/json' . "\r\n";
        $response .= "\r\n";
        $response .= "test123";

        $httpEmulator->setResponseStream(stream_for($response));

        $this->assertInternalType('array', $httpEmulator['headers']);
        $this->assertCount(2, $httpEmulator['headers']);
        $this->assertEquals('HTTP/1.1 200 OK', $httpEmulator['headers'][0]);
        $this->assertEquals('Content-Type: application/json', $httpEmulator['headers'][1]);
    }
}
