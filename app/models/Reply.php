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

//    private $config;
    public function initialize(){

//        $this->config = $this->getDI()->getShared('config');
    }

    public function addReply($params){

        $reply = new Reply();
        $reply->targetId = $params['targetId'];
        $reply->targetType = $params['targetType'];
        $reply->publisher = $params['publisher'];
        $reply->body = $params['body'];
        $reply->parsedBody = $this->parseBody($params['body']);
        $reply->createAt = $params['createAt'];
        $reply->updateAt = $params['updateAt'];

        if($reply->save()){
            return true;
        }
        return false;
    }

    public function parseBody($body){

        $patterns = [
            "/\@([a-zA-Z0-9_]+[\s]+)/",
            "/(((https?|ftp)\:\/\/)?".
            "([a-z0-9-.]*)\.([a-z]{2,3})".
            "(\:[0-9]{2,5})?".
            "(\/([a-z0-9+\$_-]\.?)+)*\/?".
            "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?".
            "(#[a-z_.-][a-z0-9+\$_.-]*)?)/"
        ];
        $replacements = [
            '@<a href="/m/$1" target="_blank">$1</a>',
            '<a href="$1" target="_blank">$1</a>'
        ];

        $parsedBody = preg_replace($patterns, $replacements, $body);
        return $parsedBody;
    }

    public function getReplies($targetId, $targetType){

        $replies = self::find([
            [
                'targetId'=>$targetId,
                'targetType'=>$targetType
            ],
            // sort by time ? json vs. php
            'sort'=>['createAt'=>1]
        ]);

        return $replies;
    }


}