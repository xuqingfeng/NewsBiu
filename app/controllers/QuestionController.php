<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/15
 */
class QuestionController extends BaseController {

    public function qAction() {

        $date = $this->dispatcher->getParam('date');
        $time = $this->dispatcher->getParam('time');

        if ($this->request->isGet()) {
            $question = new Question();
            $q = $question->getQuestion($date, $time);
            $reply = new Reply();
            $replies = $reply->getReplies("$date/$time", 'question');
            $voteValue = 0;
            if ($this->session->has('auth')) {
                $user = new User();
                $auth = $this->session->get('auth');
                $voter = $auth['name'];
                $params = [
                    'targetId'   => "$date/$time",
                    'targetType' => 'question',
                    'voter'      => $voter
                ];
                $voteValue = $user->getVoteValue($params);
            }
            if ($q) {
                $this->view->setVars([
                    'question'  => $q,
                    'replies'   => $replies,
                    'date'      => $date,
                    'time'      => $time,
                    'type'      => 'question',
                    'voteValue' => $voteValue
                ]);
            } else {
                return $this->dispatcher->forward([
                    'controller' => 'error',
                    'action'     => 'index'
                ]);
            }
        }else if($this->request->isPost()){
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
                    $this->response->redirect($this->config->environment->homepage . "/q/$date/$time", true);
                } else {
                    return $this->dispatcher->forward([
                        'controller' => 'error',
                        'action'     => 'index'
                    ]);
                }
            } else {
//                echo 'csrf fail';
//                $this->view->disable();
                return $this->dispatcher->forward([
                    'controller' => 'error',
                    'action'     => 'index'
                ]);
            }
        }


    }

    public function newAction() {

        if ($this->request->isGet()) {

        } else if ($this->request->isPost()) {

            if ($this->security->checkToken()) {
                $title = $this->request->getPost('title');
                $body = $this->request->getPost('body');

                if (isset($title)) {
                    $question = new Question();
                    $user = new User();
                    $publisher = $user->getNameBySession();
                    $now = date('Y-m-d H:i:s');
                    $params = [
                        'date'      => date('Ymd'),
                        'time'      => date('His'),
                        'title'     => $title,
                        'body'      => $body,
                        'publisher' => $publisher,
                        'createAt'  => $now,
                        'updateAt'  => $now
                    ];
                    if ($question->addQuestion($params)) {
                        $this->response->redirect($this->config->environment->homepage . '/q/' . $params['date'] . '/' . $params['time'], true);
                    } else {
                        return $this->dispatcher->forward([
                            'controller' => 'error',
                            'action'     => 'index'
                        ]);
                    }
                } else {
                    echo 'no title';
                }
            } else {
                echo 'csrf fail';
            }
            $this->view->disable();
        }
    }

}