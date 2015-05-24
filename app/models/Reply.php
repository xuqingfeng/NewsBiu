<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/27
 */
class Reply extends \Phalcon\Mvc\Collection {

    public $targetId;
    public $targetType;
    public $publisher;
    public $body;
    // @ and link
    public $parsedBody;
    public $createAt;
    public $updateAt;

    private $escaper;
    private $parsedown;

    public function initialize() {

        $this->parsedown = $this->getDI()->getShared('parsedown');
        $this->escaper = $this->getDI()->getShared('escaper');
    }

    public function addReply($params) {

        $reply = new Reply();
        $reply->targetId = $params['targetId'];
        $reply->targetType = $params['targetType'];
        $reply->publisher = $params['publisher'];
        $reply->body = $params['body'];
//        $reply->parsedBody = $this->escaper->escapeHtml($this->parseBody($params['body']));
        $site = new Site();
        $reply->parsedBody = $site->parseMentionedUsers($this->parsedown->setMarkupEscaped(true)->text($params['body']));
        $reply->createAt = $params['createAt'];
        $reply->updateAt = $params['updateAt'];

        if ($reply->save()) {
            // notify
            $mentionedUsers = $site->getMentionedUsers($params['body']);
            if (!empty($mentionedUsers)) {
                $now = date('Y-m-d H:i:s');
                $notification = new Notification();
                $p = [
                    'sender'   => $params['publisher'],
                    'receiver' => '',
                    'type'     => 2,
                    'topic'    => $reply->parsedBody,
                    'link'     => '',
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

    // change to site-parseAtUser
//    public function parseBody($body){
//
//        $patterns = [
//            "/\@([a-zA-Z0-9_]+[\s]+)/",
////            "/(((https?|ftp)\:\/\/)?".
////            "([a-z0-9-.]*)\.([a-z]{2,3})".
////            "(\:[0-9]{2,5})?".
////            "(\/([a-z0-9+\$_-]\.?)+)*\/?".
////            "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?".
////            "(#[a-z_.-][a-z0-9+\$_.-]*)?)/"
//        ];
//        $replacements = [
//            '@<a href="/m/$1" target="_blank">$1</a>',
////            '<a href="$1" target="_blank">$1</a>'
//        ];
//
//        $parsedBody = preg_replace($patterns, $replacements, $body);
//        return $parsedBody;
//    }

    public function getReplies($targetId, $targetType) {

        $replies = self::find([
            [
                'targetId'   => $targetId,
                'targetType' => $targetType
            ],
            // sort by time ? json vs. php
            'sort' => ['createAt' => 1]
        ]);

        return $replies;
    }


}