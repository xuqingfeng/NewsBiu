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
}