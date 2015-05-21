<?php
/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/5/21
 */

class Notification extends \Phalcon\Mvc\Collection {

    public $sender;
    public $receiver;
    // 1-mention(news,question body;reply) 2-reply(receiver is publisher) 3-vote up 4-vote down
    public $type;
    public $topic;
    public $link;
    public $createAt;
    public $updateAt;

    public function addNotification($params){

        $notification = new Notification();
        $notification->sender = $params['sender'];
        $notification->receiver = $params['receiver'];
        $notification->type = $params['type'];
        $notification->topic = $params['topic'];
        $notification->link = $params['link'];
        $notification->createAt = $params['createAt'];
        $notification->updateAt = $params['updateAt'];

        if($notification->save()){
            return true;
        }
        return false;
    }

}