<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/13
 */
class News extends \Phalcon\Mvc\Collection {

    public $date;
    public $time;
    public $title;
    public $link;
    public $showLink;
    public $body;
    public $parsedBody;
    public $publisher;
    public $voteUp;
    public $voteDown;
    public $hotScore;
    public $createAt;
    public $updateAt;

    private $e;
    private $limit;
    private $parsedown;

    public function initialize() {

        $this->e = new \Phalcon\Escaper();
        $this->limit = 10;
        $this->parsedown = $this->getDI()->getShared('parsedown');
    }

    public function getDomain($url) {

        $components = parse_url($url);
        if (isset($components['host'])) {
            return $components['host'];
        }

        return 'unknown';
    }

    public function addNews($params) {

        $news = new News();
        $news->date = $params['date'];
        $news->time = $params['time'];
        $news->title = $params['title'];
//        $news->link = $this->e->escapeUrl($params['link']);
        $news->link = $params['link'];
        $news->showLink = $params['showLink'];
        $news->body = $params['body'];
//        $news->parsedBody = $this->e->escapeHtml($this->parsedown->text($params['body']));
        $news->parsedBody = $this->parsedown->text($params['body']);
        $news->publisher = $params['publisher'];
        $news->voteUp = 0;
        $news->voteDown = 0;
        $news->hotScore = 0;
        $news->createAt = $params['createAt'];
        $news->updateAt = $params['updateAt'];
        if ($news->save()) {
            return true;
        }

        return false;
    }

    public function getNews($date, $time) {

        $news = self::findFirst([
            [
                'date' => $date,
                'time' => $time
            ]
        ]);

        return $news;
    }

    public function getLatestNews(){

        $news = self::find([
            [],
            'limit'=>$this->limit
        ]);

        return $news;
    }
}