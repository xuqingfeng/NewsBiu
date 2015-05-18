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
    public $comments;
    public $hotScore;
    public $createAt;
    public $updateAt;

    private $e;
    private $limit;
    private $parsedown;

    private $user;

    public function initialize() {

        $this->e = new \Phalcon\Escaper();
        $this->limit = 10;
        $this->parsedown = $this->getDI()->getShared('parsedown');

        $this->user = new User();
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
        $news->comments = 0;
        $news->hotScore = 0;
        $news->createAt = $params['createAt'];
        $news->updateAt = $params['updateAt'];
        if ($news->save()) {
            return true;
        }

        return false;
    }

    public function addComment($date, $time){

        $news = self::findFirst([
            [
                'date'=>$date,
                'time'=>$time
            ]
        ]);
        $news->comments++;
        $news->save();
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

    public function getLatestNews() {

        $news = self::find([
            [],
            'limit' => $this->limit
        ]);

        return $news;
    }

    public function voteUp($params) {

        $news = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($news) {
            // publisher can't vote
            if ($params['voter'] == $news->publisher) {
                return;
            }
            $p = [
                'name'      => $news->publisher,
                'voteValue' => $params['voteValue']
            ];
            $now = date('Y-m-d H:i:s');
//            $news->voteUp = $news->voteUp + 1;
            $news->voteUp++;
            $news->updateAt = $now;
            $news->save();

            // don't use !!
            // $this->user->voteUp($p);
            $user = new User();
            $user->votedUp($p);
        }
    }

    public function voteDown($params) {

        $news = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($news) {
            // publisher can't vote
            if ($params['voter'] == $news->publisher) {
                return;
            }
            $p = [
                'name'      => $news->publisher,
                'voteValue' => $params['voteValue']
            ];
            $now = date('Y-m-d H:i:s');
//            $news->voteDown = $news->voteDown + 1;
            $news->voteDown++;
            $news->updateAt = $now;
            $news->save();

//            $this->user->voteDown($p);
            $user = new User();
            $user->votedDown($p);
        }

    }

    public function cancelVote($params) {

        $news = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($news) {
            // publisher can't vote
            if ($params['voter'] == $news->publisher) {
                return;
            }
            $p = [
                'name'      => $news->publisher,
                'voteValue' => $params['voteValue']
            ];
            $now = date('Y-m-d H:i:s');
            if (1 === $params['voteValue']) {
                $news->voteUp = $news->voteUp - 1;
                $news->updateAt = $now;
                $news->save();
            } else if (0 === $params['voteValue']) {
                // do nothing
            } else if (-1 === $params['voteValue']) {
                $news->voteDown = $news->voteDown - 1;
                $news->updateAt = $now;
                $news->save();
            }

            $user = new User();
            $user->cancelVote($p);
        } else {
            // news does not exist
        }
    }

}