<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/27
 */
namespace Test;

class SiteTest extends \UnitTestCase {

    private $site;
    public function setUp(){

        $this->site = new \Site();
    }

    public function testParseMentionedUsers(){

        $parsedBody = $this->site->parseMentionedUsers('@xuqingfeng hi');
        $this->assertEquals('@<a href="/m/xuqingfeng " target="_blank">xuqingfeng </a>hi', $parsedBody, 'not cool');
    }

    public function testGetMentionedUsers(){

        $users = $this->site->getMentionedUsers('@xuqingfeng @jsxqf yo');
        $usersExpected = [
          'xuqingfeng','jsxqf'
        ];
        $this->assertEquals($usersExpected, $users, 'not cool');
    }


}