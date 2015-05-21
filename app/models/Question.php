<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/15
 */
class Question extends \Phalcon\Mvc\Collection {

    public $date;
    public $time;
    public $title;
    public $body;
    public $parsedBody;
    public $publisher;
    public $voteUp;
    public $voteDown;
    public $comments;
    public $hotScore;
    public $createAt;
    public $updateAt;

    private $limit;
    private $parsedown;
    private $escaper;

    public function initialize() {

        $this->limit = 10;
        $this->parsedown = $this->getDI()->getShared('parsedown');
        $this->escaper = $this->getDI()->getShared('escaper');
    }

    public function addQuestion($params) {

        $question = new Question();
        $question->date = $params['date'];
        $question->time = $params['time'];
        $question->title = $this->escaper->escapeHtml($params['title']);
        $question->body = $params['body'];
        $question->parsedBody = $this->parsedown->setMarkupEscaped(true)->text($params['body']);
        $question->publisher = $params['publisher'];
        $question->voteUp = 0;
        $question->voteDown = 0;
        $question->comments = 0;
        $question->hotScore = 0;
        $question->createAt = $params['createAt'];
        $question->updateAt = $params['updateAt'];

        if ($question->save()) {
            return true;
        }

        return false;
    }

    public function addComments($date, $time) {

        $question = self::findFirst([
            [
                'date' => $date,
                'time' => $time
            ]
        ]);
        $question->comments++;
        $question->save();
    }

    public function getQuestion($date, $time) {

        $question = self::findFirst([
            [
                'date' => $date,
                'time' => $time
            ]
        ]);

        return $question;
    }

    public function getLatestQuestions($date) {

        $questions = self::find([
            [
                'date' => $date
            ],
            'sort'  => ['hotScore' => -1],
            'limit' => $this->limit
        ]);

        return $questions;
    }

    public function getQuestionsByPage($page) {

        $questions = self::find([
            'sort'  => ['createAt' => -1],
            'limit' => $this->limit,
            'skip'  => $this->limit * ($page - 1)
        ]);

        return $questions;
    }

    public function voteUp($params) {

        $question = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($question) {
            if ($params['voter'] == $question->publisher) {
                return;
            }
            $p = [
                'name'      => $question->publisher,
                'voteValue' => $params['voteValue']
            ];
            $now = date('Y-m-d H:i:s');
            $question->voteUp++;
            $question->updateAt = $now;
            $question->save();

            $user = new User();
            $user->votedUp($p);

        }
    }

    public function voteDown($params) {

        $question = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($question) {
            if ($params['voter'] == $question->publisher) {
                return;
            }
            $p = [
                'name'      => $question->publisher,
                'voteValue' => $params['voteValue']
            ];
            $now = date('Y-m-d H:i:s');
            $question->voteDown++;
            $question->updateAt = $now;
            $question->save();

            $user = new User();
            $user->votedDown($p);
        }

    }

    public function cancelVote($params) {

        $question = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($question) {
            if ($params['voter'] == $question->publisher) {
                return;
            }
            $p = [
                'name'      => $question->publisher,
                'voteValue' => $params['voteValue']
            ];
            $now = date('Y-m-d H:i:s');
            if (1 === $params['voteValue']) {
                $question->voteUp = $question->voteUp - 1;
                $question->updateAt = $now;
                $question->save();
            } else if (0 === $params['voteValue']) {
                // do nothing
            } else if (-1 === $params['voteValue']) {
                $question->voteDown = $question->voteDown - 1;
                $question->updateAt = $now;
                $question->save();
            }

            $user = new User();
            $user->cancelVote($p);
        } else {

        }
    }

    public function addScore($params) {

        $question = self::findFirst([
            [
                'date' => $params['date'],
                'time' => $params['time']
            ]
        ]);
        if ($question) {
            $question->hotScore += $params['scoreValue'];
            $question->save();
        }
    }


}