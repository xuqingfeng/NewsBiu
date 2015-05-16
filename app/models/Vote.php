<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/27
 */
class Vote extends \Phalcon\Mvc\Collection {

    public $targetId;
    public $targetType;
    public $voter;
    public $voteValue;
    public $createAt;
    public $updateAt;

    private $news;
    private $question;

    public function initialize() {

        $this->news = new News();
        $this->question = new Question();
    }

    public function addVote($params) {

        $vote = new Vote();
        $vote->targetId = $params['targetId'];
        $vote->targetType = $params['targetType'];
        $vote->voter = $params['voter'];
        $vote->voteValue = $params['voteValue'];
        $vote->createAt = $params['createAt'];
        $vote->updateAt = $params['updateAt'];

        if ($vote->save()) {
            return true;
        }

        return false;
    }

    public function getVote($params) {

        $vote = self::findFirst([
            [
                'targetId'   => $params['targetId'],
                'targetType' => $params['targetType'],
                'voter'      => $params['voter']
            ]
        ]);

        return $vote;
    }

    // cal user reputation && news or question vote after vote !

    public function up($params) {

        // still can vote when is voter..
        $v = self::findFirst([
            [
                'targetId'   => $params['targetId'],
                'targetType' => $params['targetType'],
                'voter'      => $params['voter']
            ]
        ]);

        // isset is wrong - wtf
        if ($v) {
            // vote before
            $now = date('Y-m-d H:i:s');
            $v->voteValue = 1;
            $v->updateAt = $now;
            $v->save();
        } else {
            // else create
            $vote = new Vote();
            $vote->addVote($params);
        }

        // update news/question
        $ids = explode('/', $params['targetId']);
        $p = [
            'date'      => $ids[0],
            'time'      => $ids[1],
            'voter'     => $params['voter'],
            'voteValue' => $params['voteValue']
        ];
        if ('news' == $params['targetType']) {
            $this->news->voteUp($p);
        } else if ('question' == $params['targetType']) {
            $this->question->voteUp($p);
        }

    }

    public function down($params) {

        $vote = self::findFirst([
            [
                'targetId'   => $params['targetId'],
                'targetType' => $params['targetType'],
                'voter'      => $params['voter']
            ]
        ]);
        if ($vote) {
            $now = date('Y-m-d H:i:s');
            $vote->voteValue = -1;
            $vote->updateAt = $now;
            $vote->save();
        } else {
            $this->addVote($params);
        }

        $ids = explode('/', $params['targetId']);
        $p = [
            'date'      => $ids[0],
            'time'      => $ids[1],
            'voter'     => $params['voter'],
            'voteValue' => $params['voteValue']
        ];
        if ('news' == $params['targetType']) {
            $this->news->voteDown($p);
        } else if ('question' == $params['targetType']) {
            $this->question->voteDown($p);
        }

    }

    public function cancel($params) {

        $vote = self::findFirst([
            [
                'targetId'   => $params['targetId'],
                'targetType' => $params['targetType'],
                'voter'      => $params['voter']
            ]
        ]);

        if ($vote) {
            // vote before
            $originVoteValue = $vote->voteValue;
            // not delete
            $now = date('Y-m-d H:i:s');
            $vote->voteValue = 0;
            $vote->updateAt = $now;
            $vote->save();
            $ids = explode('/', $params['targetId']);
            $p = [
                'date'      => $ids[0],
                'time'      => $ids[1],
                'voter'     => $params['voter'],
                'voteValue' => $originVoteValue
            ];
            if ('news' == $params['targetType']) {
                $this->news->cancelVote($p);
            } else if ('question' == $params['targetType']) {
                $this->question->cancelVote($p);
            }

        } else {
            // else do nothing
        }
    }

}