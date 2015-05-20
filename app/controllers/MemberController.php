<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/7
 */
class MemberController extends BaseController {

    private static $githubAuthorizeUrl = 'https://github.com/login/oauth/authorize';
    private static $githubRedirectUrl;

    public function initialize() {

        if ('dev' == STATE) {
            self::$githubRedirectUrl = 'http://newsbiu.dev/member/callback';
        } else if ('prd' == STATE) {
            self::$githubRedirectUrl = 'https://newsbiu.org/member/callback';
        }
    }

    // member login
    public function loginAction() {

        // fake xuqingfeng
//        $this->session->set('auth', [
//            'name' => 'xuqingfeng',
//            'role' => 'root'
//        ]);
//        $this->session->set('auth', [
//            'name' => 'jsxqf',
//            'role' => 'member'
//        ]);
//        $this->response->redirect($this->config->environment->homepage, true);

//        var_dump($this->request->getHTTPReferer());
        $this->session->set('referer', $this->request->getHTTPReferer());
        $this->response->redirect(self::$githubAuthorizeUrl . "?client_id=" . $this->config->application->githubClientID . "&redirect_uri=" . self::$githubRedirectUrl . "&state=" . $this->config->application->githubState, true);
    }

    public function logoutAction() {

        $this->session->destroy('auth');
        $this->response->redirect($this->config->environment->homepage, true);
    }

    // to get access_token
    public function callbackAction() {

        $code = $this->request->getQuery('code');
        $user = new User();
        if (!empty($code)) {
            $accessToken = $user->getGithubAccessToken($code);
            if (!empty($accessToken)) {
                // get user info
                $userInfo = $user->getGithubUserInfo($accessToken);
                if (!empty($userInfo)) {
                    // verify user
                    $now = date('Y-m-d H:i:s');
                    $params = [
                        'name'       => $userInfo['login'],
                        'showName'   => $userInfo['name'],
                        'avatarUrl'  => $userInfo['avatar_url'],
                        'email'      => $userInfo['email'],
                        'from'       => 'github',
                        'role'       => 'root',
                        // reputation start at ?
                        'reputation' => $userInfo['public_repos'],
                        'createAt'   => $now,
                        'updateAt'   => $now
                    ];
                    if ($user->userExist($userInfo['login'])) {
                        $user->updateUserInfo($params);
                        echo 'update user';
                    } else {
                        $user->addUser($params);
                        echo 'add user';
                    }
                    $auth = [
                        'name' => $params['name'],
                        'role' => $params['role']
                    ];
                    $this->session->set('auth', $auth);

                    // redirect to refer
                    if ($this->session->has('referer')) {
                        $referer = $this->session->get('referer');
                        if (strpos($referer, $this->config->environment->homepage) !== false) {
                            $this->response->redirect($referer, true);
                        } else {
                            $this->response->redirect($this->config->environment->homepage, true);
                        }
                        // phpsession cookie exist in browser
                        $this->session->remove('referer');
                    } else {
//                        return $this->dispatcher->forward([
//                            'controller' => 'index',
//                            'action'     => 'index'
//                        ]);
                        $this->response->redirect($this->config->environment->homepage, true);
                    }
                } else {
                    echo 'no user info';
                }
            } else {
                echo 'no access token';
            }

        } else {
            echo 'access token error';
        }

        $this->view->disable();
        // to get github user info
    }

    // member center
    public function mAction() {

        $name = $this->dispatcher->getParam('name');
        $user = new User();
        $u = $user->getUserByName($name);
        if ($u) {
            $this->view->setVar('user', $u);
        } else {
            return $this->dispatcher->forward([
                'controller' => 'error',
                'action'     => 'index'
            ]);
        }
    }

    // member settings
    public function settingsAction() {

    }

    // notifications
    public function notificationsAction() {

    }

    // vote up
    public function voteAction() {

        if ($this->request->isPost()) {
            $targetId = $this->request->getPost('targetId');
            $targetType = $this->request->getPost('targetType');
            $voteValue = (int)$this->request->getPost('voteValue');
            $user = new User();
            $voter = $user->getNameBySession();
            if (isset($targetId) && isset($targetType) && isset($voteValue)) {
                // target exist?
                $vote = new Vote();
                $now = date('Y-m-d H:i:s');
                $params = [
                    'targetId'   => $targetId,
                    'targetType' => $targetType,
                    'voter'      => $voter,
                    'voteValue'  => 0,
                    'createAt'   => $now,
                    'updateAt'   => $now
                ];
                if ($voteValue === 1) {
                    // vote up
                    $params['voteValue'] = 1;
                    // weird - safe
                    $vote->cancel($params);
                    $vote->up($params);
                } else if ($voteValue === 0) {
                    $vote->cancel($params);
                } else if ($voteValue === -1) {
                    $params['voteValue'] = -1;
                    $vote->cancel($params);
                    $vote->down($params);
                }

                // get vote count
                $voteCount = 0;
                $ids = explode('/', $params['targetId']);
                if ('news' == $params['targetType']) {
                    $news = new News();
                    $n = $news->getNews($ids[0], $ids[1]);
                    $voteCount = $n->voteUp - $n->voteDown;
                } else if ('question' == $params['targetType']) {
                    $question = new Question();
                    $q = $question->getQuestion($ids[0], $ids[1]);
                    $voteCount = $q->voteUp - $q->voteDown;
                }

                $msg = [
                    'success'   => true,
                    'voteCount' => $voteCount,
                    'message'   => 'vote success'
                ];

            } else {
                $msg = [
                    'success' => false,
                    'message' => 'Missing parameters !'
                ];
            }
        } else {
            $msg = [
                'success' => false,
                'message' => 'Post is required !'
            ];
        }

        echo json_encode($msg);
        $this->view->disable();
        exit;
    }

}