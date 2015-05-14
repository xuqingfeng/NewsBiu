<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/27
 */
namespace Test;

class ReplyTest extends \UnitTestCase {

    private $reply;
    public function setUp(){

        $this->reply = new \Reply();
    }

    public function testParseBody(){

        $parsedBody = $this->reply->parseBody('@xuqingfeng baidu.com');
        $this->assertEquals('@<a href="/m/xuqingfeng " target="_blank">xuqingfeng </a><a href="baidu.com" target="_blank">baidu.com</a>', $parsedBody, 'not good');
    }

}