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
    public $hotScore;
    public $createAt;
    public $updateAt;

    private $limit;
    private $parsedown;

    public function initialize() {

        $this->limit = 10;
        $this->parsedown = $this->getDI()->getShared('parsedown');
    }

    public function addQuestion($params) {

        $question = new Question();
        $question->date = $params['date'];
        $question->time = $params['time'];
        $question->title = $params['title'];
        $question->body = $params['body'];
        $question->parsedBody = $this->parsedown->text($params['body']);
        $question->publisher = $params['publisher'];
        $question->voteUp = 0;
        $question->voteDown = 0;
        $question->hotScore = 0;
        $question->createAt = $params['createAt'];
        $question->updateAt = $params['updateAt'];

        if ($question->save()) {
            return true;
        }

        return false;
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

    public function getLatestQuestions() {

        $questions = self::find([
            [],
            'limit' => $this->limit
        ]);

        return $questions;
    }


}