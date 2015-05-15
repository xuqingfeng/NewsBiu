<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/14
 */
class Security extends \Phalcon\Mvc\User\Plugin {

    public function getAcl() {

//        if (!isset($this->persistent->acl)) {
        $acl = new \Phalcon\Acl\Adapter\Memory();
        $acl->setDefaultAction(\Phalcon\Acl::DENY);

        $roles = [
            'guest'  => new \Phalcon\Acl\Role('guest'),
            'member' => new \Phalcon\Acl\Role('member'),
            'admin'  => new \Phalcon\Acl\Role('admin'),
            'root'   => new \Phalcon\Acl\Role('root')
        ];
        $acl->addRole($roles['guest']);
        $acl->addRole($roles['member'], $roles['guest']);
        $acl->addRole($roles['admin'], $roles['member']);
        $acl->addRole($roles['root'], $roles['admin']);

        $allResources = [
            'guestResources'  => [
                'error'    => ['index'],
                'index'    => ['index', 'info', 'cookie'],
                'member'   => ['login', 'callback'],
                'n'        => ['index'],
                'news'     => ['list', 'n'],
                'question' => ['list', 'q']
            ],
            'memberResources' => [
                'member'   => ['logout', 'm', 'settings', 'vote', 'notifications'],
                'news'     => ['new'],
                'question' => ['new']
            ],
            'adminResources'  => [
            ],
            'rootResources'   => [
                'root' => ['createUser']
            ]
        ];

        foreach ($roles as $role) {
            foreach ($allResources[$role->getName() . 'Resources'] as $resource => $actions) {
                $acl->addResource(new \Phalcon\Acl\Resource($resource), $actions);
                foreach ($actions as $action) {
                    $acl->allow($role->getName(), $resource, $action);
                }
            }
        }

//            $this->persistent->acl = $acl;

//        }

        return $acl;
//        return $this->persistent->acl;
    }

    // before route ?
    public function beforeDispatch(\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispatcher) {

        $auth = $this->session->get('auth');
        if (isset($auth)) {
            $role = 'root';
        } else {
            $role = 'guest';
        }

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        $acl = $this->getAcl();
        $allowed = $acl->isAllowed($role, $controller, $action);
        // Dispatcher has detected a cyclic routing causing stability problems
        if ($allowed != \Phalcon\Acl::ALLOW && $controller != 'error') {
            // forward(''); does not work?
            $dispatcher->forward([
                'controller' => 'error',
                'action'     => 'index'
            ]);

            return false;
        }
    }
}