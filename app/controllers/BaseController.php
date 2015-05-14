<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/3
 */
class BaseController extends \Phalcon\Mvc\Controller {

    public function initialize() {

        $state = $this->config->environment->state;
        if ('dev' == $state) {
            $dir = 'dev';
            $min = '';
        } else {
            $dir = 'prd';
            $min = '.min';
        }
        $this->assets
            ->addCss("$dir/css/normalize$min.css")
            ->addCss("$dir/css/skeleton$min.css")
            ->addCss("$dir/css/evil-icons$min.css")
            ->addCss("$dir/css/index$min.css");

        $this->assets
            ->addJs("$dir/js/evil-icons$min.js");
    }

}