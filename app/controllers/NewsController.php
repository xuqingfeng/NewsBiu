<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/13
 */
class NewsController extends BaseController {

    public function listAction() {


    }

    public function nAction() {

        $date = $this->dispatcher->getParam('date');
        $time = $this->dispatcher->getParam('time');

        if ($this->request->isGet()) {
            $news = new News();
            $n = $news->getNews($date, $time);
            $reply = new Reply();
            $replies = $reply->getReplies("$date/$time", 'news');
            if ($n) {
                $this->view->setVars([
                    'news'    => $n,
                    'replies' => $replies
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
                $user = new User();
                $publisher = $user->getNameBySession();
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
                    $this->response->redirect($this->config->environment->homepage . "/n/$date/$time", true);
                } else {
                    return $this->dispatcher->forward([
                        'controller' => 'error',
                        'action'     => 'index'
                    ]);
                }
            } else {
                echo 'csrf fail';
                $this->view->disable();
            }
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
                    $publisher = $user->getNameBySession();
                    $news = new News();
                    $now = date('Y-m-d H:i:s');
                    $params = [
                        'date'      => date('Ymd'),
                        'time'      => date('His'),
                        'title'     => $title,
                        'link'      => $link,
                        'showLink'  => $news->getDomain($link),
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
                    // flash message
                    return $this->dispatcher->forward([
                        'controller' => 'error',
                        'action'     => 'index'
                    ]);
                }
            } else {
                echo 'csrf fail';
            }
            $this->view->disable();

        }

    }

}