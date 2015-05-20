<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/3
 */
class IndexController extends BaseController {

    public function indexAction() {

        // home or qna
        $tab = $this->dispatcher->getParam('tab');
        if (isset($tab)) {
            if ('home' != $tab && 'qna' != $tab) {
                $tab = 'home';
            }
        } else {
            if ($this->cookies->has('NEWSBIUTAB')) {
                $NEWSBIUTAB = $this->cookies->get('NEWSBIUTAB');
                $tabInCookie =  $NEWSBIUTAB->getValue();
                // if cookie is wrong - output nothing
                if ('home' != $tabInCookie && 'qna' != $tabInCookie) {
                    $tab = 'home';
                } else {
                    $tab = $tabInCookie;
                }
            }else{
                $tab = 'home';
            }
        }

        $this->cookies->set('NEWSBIUTAB', $tab, time() + 7 * 86400);
        $this->view->setVar('tab', $tab);
    }

    public function infoAction() {

        echo phpinfo();
        $this->view->disable();
    }

    public function denyAction() {

    }

    public function cookieAction(){

        if($this->cookies->has('test')){
            $testInCookie = $this->cookies->get('test');
            $test = $testInCookie->getValue();
            var_dump($test);
        }else{
            $test = 'no cookie';
            echo $test;
        }
        $this->cookies->set('test', $test, time() + 7 * 86400);
        $this->view->disable();
    }

}