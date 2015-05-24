<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/7
 */
class User extends \Phalcon\Mvc\Collection {

    public $name;
    public $showName;
    public $avatarUrl;
    public $email;
    public $role;
    public $from;
    public $reputation;
    // 0-hasn't 1-has
    public $notified;
    public $createAt;
    public $updateAt;

    public static $voteUpReputation = 5;
    public static $voteDownReputation = 2;

    private $config;
    private $session;

    public function initialize() {

        $this->config = $this->getDI()->getShared('config');
        $this->session = $this->getDI()->getShared('session');
    }

    public function userExist($name) {

        $user = self::findFirst([
            [
                'name' => $name
            ]
        ]);
        if ($user == false) {
            return false;
        }

        return true;
    }

    public function addUser($params) {

        try {
            $user = new User();
            $user->name = $params['name'];
            $user->showName = $params['showName'];
            $user->avatarUrl = $params['avatarUrl'];
            $user->email = $params['email'];
            $user->role = $params['role'];
            $user->from = $params['from'];
            $user->reputation = $params['reputation'];
            $user->notified = 1;
            $user->createAt = $params['createAt'];
            $user->updateAt = $params['updateAt'];

            if ($user->save() == false) {
                return false;
            }

            return true;
        } catch (\Phalcon\Exception $e) {
            return $e->getMessage();
        }

    }

    public function updateUserInfo($params) {

        $user = self::findFirst([
            [
                'name' => $params['name']
            ]
        ]);
        $user->showName = $params['showName'];
        $user->avatarUrl = $params['avatarUrl'];
        $user->email = $params['email'];
        $user->updateAt = $params['updateAt'];
        $user->save();
    }

    public function getUserByName($name) {

        $user = self::findFirst([
            [
                'name' => $name
            ]
        ]);

        return $user;
    }

    public function getNameBySession() {

        if ($this->session->has('auth')) {
            $auth = $this->session->get('auth');

            return $auth['name'];
        }

        return 'unknown';
    }

    /**
     * github stuff
     */
    private static $githubAccessTokenUrl = 'https://github.com/login/oauth/access_token';
    private static $githubUserInfoUrl = 'https://api.github.com/user';

    public function getGithubAccessToken($code) {

        $postData = [
            'client_id'     => $this->config->application->githubClientID,
            'client_secret' => $this->config->application->githubClientSecret,
            'code'          => $code
        ];
        // params required
        $ch = curl_init(self::$githubAccessTokenUrl . "?client_id=" . $this->config->application->githubClientID . "&client_secret=" . $this->config->application->githubClientSecret . "&code=" . $code);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // CURLOPT_HTTPHEADER - NOT - HEADER
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $ret = curl_exec($ch);
        if (curl_error($ch) !== false) {
            curl_close($ch);

            $body = json_decode($ret, true);

            return $body['access_token'];
        } else {
            curl_close($ch);

            return '';
        }

    }

    public function getGithubUserInfo($accessToken) {

        $ch = curl_init(self::$githubUserInfoUrl . "?access_token=" . $accessToken);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        $ret = curl_exec($ch);
        if (curl_error($ch) !== false) {
            curl_close($ch);

            return json_decode($ret, true);
        } else {
            curl_close($ch);

            return [];
        }
    }

    public function votedUp($params) {

        $user = self::findFirst([
            [
                'name' => $params['name']
            ]
        ]);
        if ($user) {
            $now = date('Y-m-d H:i:s');
            $user->reputation = $user->reputation + 1 * self::$voteUpReputation;
            $user->updateAt = $now;
            $user->save();
        } else {

        }
    }

    public function votedDown($params) {

        $user = self::findFirst([
            [
                'name' => $params['name']
            ]
        ]);
        if ($user) {
            $now = date('Y-m-d H:i:s');
            $user->reputation = $user->reputation - 1 * self::$voteDownReputation;
            $user->updateAt = $now;
            $user->save();
        } else {

        }
    }

    public function cancelVote($params) {

        $user = self::findFirst([
            [
                'name' => $params['name']
            ]
        ]);
        if ($user) {
            $now = date('Y-m-d H:i:s');
            if (1 === $params['voteValue']) {
                $user->reputation = $user->reputation - 1 * self::$voteUpReputation;
                $user->updateAt = $now;
                $user->save();
            } else if (0 === $params['voteValue']) {
                // do nothing
            } else if (-1 === $params['voteValue']) {
                $user->reputation = $user->reputation + 1 * self::$voteDownReputation;
                $user->updateAt = $now;
                $user->save();
            }
        } else {
            // user does not exist
        }

    }

    public function getVoteValue($params) {

        $vote = new Vote();
        $v = $vote->getVote($params);
        if ($v) {
            return $v->voteValue;
        } else {
            return 0;
        }

    }

    public function changeNotifiedState($name, $state) {

        $user = self::findFirst([
            [
                'name' => $name
            ]
        ]);
        if ($user) {
            $user->notified = $state;
            $user->save();
        }
    }

}