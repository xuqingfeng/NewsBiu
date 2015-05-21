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

    private $escaper;
    private $limit;
    private $parsedown;

//    private $user;

    public function initialize() {

        $this->limit = 10;

        $this->escaper = $this->getDI()->getShared('escaper');
        $this->parsedown = $this->getDI()->getShared('parsedown');

//        $this->user = new User();
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
        $news->title = $this->escaper->escapeHtml($params['title']);
//        $news->link = $this->escaper->escapeUrl($params['link']);
        $news->link = $params['link'];
        $news->showLink = $this->getDomain($params['link']);
        $news->body = $params['body'];
        $site = new Site();
        $news->parsedBody = $site->parseMentionedUsers($this->parsedown->setMarkupEscaped(true)->text($params['body']));
//        $news->parsedBody = $this->e->escapeHtml($this->parsedown->text($params['body']));
//        $news->parsedBody = $this->escaper->escapeHtml($this->parsedown->setMarkupEscaped(true)->text($params['body']));
        $news->publisher = $params['publisher'];
        $news->voteUp = 0;
        $news->voteDown = 0;
        $news->comments = 0;
        $news->hotScore = 0;
        $news->createAt = $params['createAt'];
        $news->updateAt = $params['updateAt'];
        if ($news->save()) {
            // notify
            $mentionedUsers = $site->getMentionedUsers($params['body']);
            if (!empty($mentionedUsers)) {
                $now = date('Y-m-d H:i:s');
                $notification = new Notification();
                $p = [
                    'sender'   => $params['publisher'],
                    'receiver' => '',
                    'type'     => 1,
                    'topic'    => $news->title,
                    'link'     => "/n/" . $params['date'] . "/" . $params['time'],
                    'createAt' => $now,
                    'updateAt' => $now
                ];
                $user = new User();
                foreach ($mentionedUsers as $u) {
                    $p['receiver'] = $u;
                    $notification->addNotification($p);
                    $user->changeNotifiedState($u, 0);
                }
            }

            return true;
        }

        return false;
    }

    public function addComment($date, $time) {

        $news = self::findFirst([
            [
                'date' => $date,
                'time' => $time
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

    public function getLatestNews($date) {

        // condition "or"?
        $news = self::find([
            [
                'date' => $date,
            ],
            'sort'  => ['hotScore' => -1],
            'limit' => $this->limit
        ]);

        return $news;
    }

    public function getNewsByPage($page) {

        $news = self::find([
            'sort'  => ['createAt' => -1],
            'limit' => $this->limit,
            'skip'  => $this->limit * ($page - 1)
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

            // notify
            $notificationParams = [
                'sender'   => $params['voter'],
                'receiver' => $news->publisher,
                'type'     => 3,
                'topic'    => $news->title,
                'link'     => "/n/" . $news->date . "/" . $news->time,
                'createAt' => $now,
                'updateAt' => $now
            ];
            $site = new Site();
            $site->voteNotify($notificationParams);
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
            $news->voteDown++;
            $news->updateAt = $now;
            $news->save();

            $user = new User();
            $user->votedDown($p);

            // notify
            $notificationParams = [
                'sender'   => $params['voter'],
                'receiver' => $news->publisher,
                'type'     => 4,
                'topic'    => $news->title,
                'link'     => "/n/" . $news->date . "/" . $news->time,
                'createAt' => $now,
                'updateAt' => $now
            ];
            $site = new Site();
            $site->voteNotify($notificationParams);
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

    /**
     * @param $params
     * vote - 5
     * vote down - -2
     * reply - 1
     */
    public function addScore($params) {

        $news = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($news) {
            $news->hotScore += $params['scoreValue'];
            $news->save();
        }

    }

}