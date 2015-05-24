<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/5/15
 */
class RootController extends BaseController {


    public function createUserAction() {

        if ($this->request->isPost()) {
            $now = date('Y-m-d H:i:s');
            $params = [
                'name'       => $this->request->getPost('name'),
                'showName'   => $this->request->getPost('showName'),
                'avatarUrl'  => $this->request->getPost('avatarUrl'),
                'email'      => $this->request->getPost('email'),
                'role'       => $this->request->getPost('role'),
                'from'       => 'github',
                'reputation' => $this->request->getPost('reputation'),
                'createAt'   => $now,
                'updateAt'   => $now
            ];
            $user = new User();
            $user->addUser($params);
        }
    }

    public function resetAction() {

        $collection = $this->dispatcher->getParam('collection');
        $name = $this->dispatcher->getParam('name');
        $value = $this->dispatcher->getParam('value');

        if ('news' == $collection) {
            $news = News::find();
            foreach ($news as $n) {
                $n->{$name} = (int)$value;
                $n->save();
                echo $n->{$name};
            }

        }

        if ('user' == $collection) {
            $user = User::find();
            foreach ($user as $u) {
                $u->{$name} = (int)$value;
                $u->save();
                echo $u->{$name};
            }
        }
        $this->view->disable();
    }
}