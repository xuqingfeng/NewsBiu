<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/15
 */
class QuestionController extends BaseController {

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
                    if($question->addQuestion($params)){
                        $this->response->redirect($this->config->environment->homepage.'/q/'.$params['date'].'/'.$params['time'], true);
                    }else{
                        return $this->dispatcher->forward([
                            'controller' => 'error',
                            'action'     => 'index'
                        ]);
                    }
                }else{
                    echo 'no title';
                }
            }else{
                echo 'csrf fail';
            }
            $this->view->disable();
        }
    }

    public function qAction() {

        $date = $this->dispatcher->getParam('date');
        $time = $this->dispatcher->getParam('time');

        $question = new Question();
        $q = $question->getQuestion($date, $time);
        if($q){
            $this->view->setVar('question', $q);
        }else{
            return $this->dispatcher->forward([
                'controller' => 'error',
                'action'     => 'index'
            ]);
        }
    }

}