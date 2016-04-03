<?php

namespace CvoTechnologies\StreamEmulation\Emulator;

use GuzzleHttp\Psr7\Request;

class HttpEmulator extends Emulator
{
    protected $response;

    public function getIncomingStream()
    {
        $options = stream_context_get_options($this->context);

        $method = 'GET';
        if (isset($options['http']['method'])) {
            $method = $options['http']['method'];
        }
        $headers = [];
        if (isset($options['http']['header'])) {
            $headerLines = explode("\r\n", $options['http']['header']);
            foreach ($headerLines as $headerLine) {
                list($header, $value) = explode(': ', $headerLine, 2);

                $headers[$header][] = $value;
            }
        }
        $body = null;
        if (isset($options['http']['content'])) {
            $body = $options['http']['content'];
        }
        $protocolVersion = 1.1;
        if (isset($options['http']['protocol_version'])) {
            $protocolVersion = $options['http']['protocol_version'];
        }

        $request = new Request($method, $this->path, $headers, $body, $protocolVersion);

        $path = $request->getUri()->getPath();
        if (!$path) {
            $path = '/';
        }
        $content = $request->getMethod() . ' ' . $path . ' HTTP/' . $request->getProtocolVersion() . "\r\n";
        foreach ($request->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                $content .= $header .': ' . $value . "\r\n";
            }
        }

        if ($request->getBody()->getSize()) {
            $content .= "\r\n";
            $content .= $request->getBody()->getContents();
        }

        return \GuzzleHttp\Psr7\stream_for($content);
    }

    public function getOutgoingStream()
    {
        return $this->getResponse()->getBody();
    }

    public function getResponse()
    {
        if ($this->response) {
            return $this->response;
        }

        return $this->response = \GuzzleHttp\Psr7\parse_response($this->getResponseStream());
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['headers']);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if ($offset === 'headers') {
            $headers = [
                'HTTP/' . $this->getResponse()->getProtocolVersion() . ' ' . $this->getResponse()->getStatusCode() . ' ' . $this->getResponse()->getReasonPhrase()
            ];
            foreach  ($this->getResponse()->getHeaders() as $header => $values) {
                foreach ($values as $value) {
                    $headers[] = $header . ': ' . $value;
                }
            }

            return $headers;
        }
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
    }
}
