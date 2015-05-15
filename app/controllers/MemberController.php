<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/7
 */
class MemberController extends BaseController {

    private static $githubAuthorizeUrl = 'https://github.com/login/oauth/authorize';
    private static $githubRedirectUrl = 'http://newsbiu.dev/member/callback';

    // member login
    public function loginAction() {

        // fake
        $this->session->set('auth', [
            'name' => 'xuqingfeng',
            'role' => 'root'
        ]);
        $this->response->redirect($this->config->environment->homepage, true);

//        $this->response->redirect(self::$githubAuthorizeUrl . "?client_id=" . $this->config->application->githubClientID . "&redirect_uri=" . self::$githubRedirectUrl . "&state=" . $this->config->application->githubScope, true);
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
                        // phpsession cookie exist in browser
                        $this->session->remove('referer');
                        $this->response->redirect($referer, true);
                    } else {
                        return $this->dispatcher->forward([
                            'controller' => 'index',
                            'action'     => 'index'
                        ]);
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
//        $this->view->setVar('name', $name);
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
    public function settingsAction(){

    }

    // notifications
    public function notificationsAction(){

    }

}