<?php

if(!defined('WP_UNINSTALL_PLUGIN')){
    die();
}

function lu_uninstall_plugin() {

}

register_uninstall_hook(__FILE__, 'lu_uninstall_plugin');
