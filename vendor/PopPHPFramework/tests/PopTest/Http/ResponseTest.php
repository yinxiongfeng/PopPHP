<?php
/**
 * Pop PHP Framework Unit Tests
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 */

namespace PopTest\Http;

use Pop\Loader\Autoloader,
    Pop\Http\Response;

// Require the library's autoloader.
require_once __DIR__ . '/../../../src/Pop/Loader/Autoloader.php';

// Call the autoloader's bootstrap function.
Autoloader::factory()->splAutoloadRegister();

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $this->assertInstanceOf('Pop\Http\Response', $r = new Response(200, array('Content-Type' => 'text/plain')));
    }

    public function testConstructorException()
    {
        $this->setExpectedException('Pop\Http\Exception');
        $r = new Response(601, array('Content-Type' => 'text/plain'));
    }

    public function testSetCode()
    {
        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setCode(404);
        $this->assertEquals(404, $r->getCode());
    }

    public function testSetCodeException()
    {
        $this->setExpectedException('Pop\Http\Exception');
        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setCode(601);
    }

    public function testSetAndGetMessage()
    {
        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setMessage('OK');
        $this->assertEquals('OK', $r->getMessage());
        $this->assertEquals('OK', Response::getMessageFromCode(200));
    }

    public function testGetMessageException()
    {
        $this->setExpectedException('Pop\Http\Exception');
        $msg = Response::getMessageFromCode(601);
    }

    public function testSetHeader()
    {
        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setHeader('Content-Type', 'text/plain');
        $r->setHeaders(array('Content-Length' => 1024, 'Content-Encoding' => 'gzip'));
        $this->assertEquals('text/plain', $r->getHeader('Content-Type'));
        $this->assertEquals(1024, $r->getHeader('Content-Length'));
        $this->assertEquals('gzip', $r->getHeader('Content-Encoding'));
    }

    public function testSetBody()
    {
        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setBody('This is a test.');
        $this->assertEquals('This is a test.', $r->getBody());
    }

    public function testOutput()
    {
        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setBody('This is a test.');
        $response = $r->getHeadersAsString() . PHP_EOL . $r->getBody();

        $this->assertContains('HTTP/1.1 200 OK', $response);
        $this->assertContains('Content-Type: text/plain', $response);
        $this->assertContains('This is a test', $response);
    }

    public function testParse()
    {
        $r = Response::parse('http://www.popphp.org/version.txt');
        $this->assertEquals('200', $r->getCode());
        $this->assertEquals('OK', $r->getMessage());
        $this->assertEquals('1.1.0', trim($r->getBody()));
        $this->assertEquals('text/plain', $r->getHeader('Content-Type'));
        $this->assertTrue($r->isSuccessful());
        $this->assertTrue(is_array($r->getHeaders()));
        $this->assertFalse($r->isError());
        $this->assertFalse($r->isRedirect());

        $r = new Response(200, array('Content-Type' => 'text/plain'));
        $r->setBody('This is a test.');
        $response = $r->getHeadersAsString() . PHP_EOL . $r->getBody();
        $r = Response::parse($response);
        $this->assertEquals('200', $r->getCode());
        $this->assertEquals('OK', $r->getMessage());
        $this->assertEquals('This is a test.', trim($r->getBody()));
    }

    public function testEncodeAndDecode()
    {
        $e = Response::encodeBody('This is a test.');
        $this->assertEquals('This is a test.', Response::decodeBody($e));

        $e = Response::encodeBody('This is a test.', 'deflate');
        $this->assertEquals('This is a test.', Response::decodeBody($e, 'deflate'));
    }

}

