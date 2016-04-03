<?php

namespace CvoTechnologies\StreamEmulation\Test\TestCase\Emulation;

use CvoTechnologies\StreamEmulation\Emulation\HttpEmulation;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class TestHttpEmulation extends HttpEmulation
{
    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _run(RequestInterface $request)
    {
        return new Response(200, [
            'Content-Type' => 'application/json'
        ], 'test123');
    }
}

class HttpEmulationTest extends \PHPUnit_Framework_TestCase
{
    public function testHeaders()
    {
        $request = 'GET / HTTP/1.1' . "\r\n";

        $testHttpEmulation = new TestHttpEmulation();
        $response = $testHttpEmulation(\GuzzleHttp\Psr7\stream_for($request))->getContents();

        $lines = explode("\r\n", $response);
        $this->assertEquals('HTTP/1.1 200 OK', $lines[0]);
        $this->assertEquals('Content-Type: application/json', $lines[1]);
        $this->assertEquals('', $lines[2]);
        $this->assertCount(4, $lines);
    }

    public function testContent()
    {
        $request = 'GET / HTTP/1.1' . "\r\n";

        $testHttpEmulation = new TestHttpEmulation();
        $response = $testHttpEmulation(\GuzzleHttp\Psr7\stream_for($request))->getContents();

        $lines = explode("\r\n", $response);
        $this->assertEquals('HTTP/1.1 200 OK', $lines[0]);
        $this->assertEquals('Content-Type: application/json', $lines[1]);
        $this->assertEquals('', $lines[2]);
        $this->assertEquals('test123', $lines[3]);
        $this->assertCount(4, $lines);
    }
}
