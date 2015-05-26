<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/13
 */
class NewsController extends BaseController {

    public function listAction() {

        $page = (int)$this->request->getQuery('p');
        $news = new News();
        if (!isset($page) || $page < 1) {
            $page = 1;
        }
        $n = $news->getNewsByPage($page);
        $pageCount = $news->getPageCount();

        if ($n) {
            $this->view->setVars([
                'news'      => $n,
                'page'      => $page,
                'pageCount' => $pageCount
            ]);
        } else {
            return $this->dispatcher->forward([
                'controller' => 'error',
                'action'     => 'index'
            ]);
        }
    }

    public function nAction() {

        $date = $this->dispatcher->getParam('date');
        $time = $this->dispatcher->getParam('time');

        $news = new News();
        if ($this->request->isGet()) {
            // js
//            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            $n = $news->getNews($date, $time);
            $reply = new Reply();
            $replies = $reply->getReplies("$date/$time", 'news');
            // login or not
            $voteValue = 0;
            $isPublisher = false;
            if ($this->session->has('auth')) {
                // check has voted
                $user = new User();
                // use two session together?
                $auth = $this->session->get('auth');
                $voter = $auth['name'];
                $params = [
                    'targetId'   => $date . '/' . $time,
                    'targetType' => 'news',
                    'voter'      => $voter
                ];
                $voteValue = $user->getVoteValue($params);

                if ($auth['name'] == $n->publisher) {
                    $isPublisher = true;
                }
            }

            if ($n) {
                $this->view->setVars([
                    'news'        => $n,
                    'seoTitle'    => $n->title,
                    'replies'     => $replies,
                    'date'        => $date,
                    'time'        => $time,
                    'type'        => 'news',
                    'voteValue'   => $voteValue,
                    'isPublisher' => $isPublisher
                ]);
            } else {
                return $this->dispatcher->forward([
                    'controller' => 'error',
                    'action'     => 'index'
                ]);
            }
        } else if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $replyContent = $this->request->getPost('reply');
                $targetType = $this->request->getPost('targetType');
                $now = date('Y-m-d H:i:s');
                // ? can't use session ??
//                $user = new User();
//                $publisher = $user->getNameBySession();
                if ($this->session->has('auth')) {
                    $auth = $this->session->get('auth');
                    $publisher = $auth['name'];
                    $params = [
                        'targetId'   => "$date/$time",
                        'targetType' => $targetType,
                        'publisher'  => $publisher,
                        'body'       => $replyContent,
                        'createAt'   => $now,
                        'updateAt'   => $now
                    ];
                    $reply = new Reply();
                    if ($reply->addReply($params)) {
                        $news->addComment($date, $time);
                        // add score
                        $p = [
                            'date'       => $date,
                            'time'       => $time,
                            'scoreValue' => 1
                        ];
                        $news->addScore($p);
                        $this->response->redirect($this->config->environment->homepage . "/n/$date/$time", true);
                    } else {
                        return $this->dispatcher->forward([
                            'controller' => 'error',
                            'action'     => 'index'
                        ]);
                    }
                } else {
                    return $this->dispatcher->forward([
                        'controller' => 'error',
                        'action'     => 'index'
                    ]);
                }

            } else {
//                echo 'csrf fail';
                return $this->dispatcher->forward([
                    'controller' => 'error',
                    'action'     => 'index'
                ]);
            }
            $this->view->disable();
        }


    }

    /**
     * create new news
     */
    public function newAction() {

        if ($this->request->isGet()) {

        } else if ($this->request->isPost()) {

            if ($this->security->checkToken()) {
                $title = $this->request->getPost('title');
                $link = $this->request->getPost('link');
                $body = $this->request->getPost('body');

                if (isset($title) && isset($link)) {
                    $user = new User();
                    // work?
                    $publisher = $user->getNameBySession();
                    $news = new News();
                    $now = date('Y-m-d H:i:s');
                    $params = [
                        'date'      => date('Ymd'),
                        'time'      => date('His'),
                        'title'     => $title,
                        'link'      => $link,
                        'body'      => $body,
                        'publisher' => $publisher,
                        'createAt'  => $now,
                        'updateAt'  => $now
                    ];
                    if ($news->addNews($params)) {
                        $this->response->redirect($this->config->environment->homepage . "/n/" . $params['date'] . "/" . $params['time'], true);
                    } else {
                        return $this->dispatcher->forward([
                            'controller' => 'error',
                            'action'     => 'index'
                        ]);
                    }
                } else {
                    // flash message ?
                    return $this->dispatcher->forward([
                        'controller' => 'error',
                        'action'     => 'index'
                    ]);
                }
            } else {
//                echo 'csrf fail';
                return $this->dispatcher->forward([
                    'controller' => 'error',
                    'action'     => 'index'
                ]);
            }
            $this->view->disable();

        }
    }

}