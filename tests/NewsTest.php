<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/26
 */
namespace Test;

class NewsTest extends \UnitTestCase {

    private $news;

    public function setUp(){

        $this->news = new \News();
    }

    public function testGetDomain(){

        $trello = $this->news->getDomain('https://trello.com/b/3dYIQhqS/newsbiu');
        $this->assertEquals('trello.com', $trello);
    }

}