<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Mvc\Magic;

class Configuration {














    private $lang = 'en';
    private $lang_list = array(
            'en',
            'fr',
            'es',
            'nl'
        );
    private $base_url = 'http://example.com/phpauth2.0/';
    private $salt_1 = 'us_1dUDN4N-53/dkf7Sd?vbc_due1d?df!feg';
    private $salt_2 = 'Yu23ds09*d?u8SDv6sd?usi$_YSdsa24fd+83';
    private $salt_3 = '63fds.dfhsAdyISs_?&jdUsydbv92bf54ggvc';
    private $admin_level = 99;
    private $table_activations = 'activations';
    private $table_log = 'log';
    private $table_resets = 'resets';
    private $table_sessions = 'sessions';
    private $table_users = 'users';
    private $session_duration = "+1 month";
}
