<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/14
 *
 * extends nothing, make tests pass
 */
class Site {

    public function getDomain($url) {

        $components = parse_url($url);
        if (isset($components['host'])) {
            return $components['host'];
        }

        return 'unknown';
    }


    public function parseMentionedUsers($body) {

        $patterns = [
            "/\@([a-zA-Z0-9_]+[\s]+)/"
        ];
        $replacements = [
            '@<a href="/m/$1" target="_blank">$1</a>'
        ];

        $parsedBody = preg_replace($patterns, $replacements, $body);

        return $parsedBody;
    }

    public function getMentionedUsers($body) {

        $users = [];
        preg_match_all("/\@([a-zA-Z0-9_]+[\s]+)/", $body, $matches);
        if (!empty($matches[0])) {
            $uniqueMatches = array_unique($matches[0]);
            foreach ($uniqueMatches as $m) {
                $m = trim($m);
                $arr = explode('@', $m);
                $users[] = $arr[1];
            }
        }

        return $users;
    }

    public function voteNotify($params) {

        $p = [
            'sender'   => $params['sender'],
            'receiver' => $params['receiver'],
            'type'     => $params['type'],
            'topic'    => $params['topic'],
            'link'     => $params['link'],
            'createAt' => $params['createAt'],
            'updateAt' => $params['updateAt']
        ];
        $notification = new Notification();
        $notification->addNotification($p);
        $user = new User();
        $user->changeNotifiedState($params['receiver'], 0);
    }
}