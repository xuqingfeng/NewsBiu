<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/27
 */

class Vote extends \Phalcon\Mvc\Collection {

    public $targetId;
    public $targetType;
    public $publisher;
    public $voteUp;
    public $voteDown;
    public $createAt;
    public $updateAt;

}