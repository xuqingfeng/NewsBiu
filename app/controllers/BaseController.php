<?php

/**
 * Author: xuqingfeng <js-xqf@hotmail.com>
 * Date: 15/4/3
 */
class BaseController extends \Phalcon\Mvc\Controller {

    public function initialize() {

        if ('dev' == STATE) {
            $dir = 'dev';
            $min = '';
        } else if ('prd' == STATE) {
            $dir = 'prd';
            $min = '.min';
        }
        $this->assets
            ->addCss("$dir/css/normalize$min.css")
            ->addCss("$dir/css/skeleton$min.css")
            ->addCss("$dir/css/evil-icons$min.css")
            ->addCss("$dir/css/sweet-alert$min.css")
            ->addCss("$dir/css/ie9$min.css")
            ->addCss("$dir/css/index$min.css");

        // needed ?
        $this->assets
            ->collection('js-evil-icons')
            ->addJs("$dir/js/evil-icons$min.js");

        $this->assets
            ->addJs("$dir/js/jquery$min.js")
            ->addJs("$dir/js/sweet-alert$min.js");
    }

}