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

    public function getVote($params){

        $vote = self::findFirst([
            [
                'targetId'=>$params['targetId'],
                'targetType'=>$params['targetType'],
                'voter'=>$params['voter']
            ]
        ]);

        return $vote;
    }

    // cal user reputation && news or question vote after vote !

    public function up($params) {

        $vote = self::findFirst([
            [
                'targetId'   => $params['targetId'],
                'targetType' => $params['targetType'],
                'voter'      => $params['voter']
            ]
        ]);

        if (isset($vote)) {
            // vote before
            $now = date('Y-m-d H:i:s');
            $vote->voteValue = 1;
            $vote->updateAt = $now;
            $vote->save();
        } else {
            // else create
            $this->addVote($params);
        }

        // update news/question
        if ('news' == $params['targetType']) {
            $this->news->voteUp($params);
        } else if ('question' == $params['targetType']) {
            $this->question->voteUp($params);
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
        if(isset($vote)){
            $now = date('Y-m-d H:i:s');
            $vote->voteValue = -1;
            $vote->updateAt = $now;
            $vote->save();
        }else{
            $this->addVote($params);
        }

        if('news'==$params['targetType']){
            $this->news->voteDown($params);
        }else if('question'==$params['targetType']){
            $this->question->voteDown($params);
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

        if (isset($vote)) {
            // vote before
            $originVoteValue = $vote->voteValue;
            $ids = explode('/', $params['targetId']);
            $p = [
                'date'      => $ids[0],
                'time'      => $ids[1],
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