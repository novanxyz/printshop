<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'expressionengine';
$active_record = TRUE;

$db['expressionengine']['hostname'] = 'localhost';
$db['expressionengine']['username'] = 'adorama';
$db['expressionengine']['password'] = 'adorama';
$db['expressionengine']['database'] = 'adorama';
$db['expressionengine']['dbdriver'] = 'mysqli';
$db['expressionengine']['pconnect'] = FALSE;
$db['expressionengine']['dbprefix'] = 'exp_';
$db['expressionengine']['swap_pre'] = 'exp_';
$db['expressionengine']['db_debug'] = TRUE;
$db['expressionengine']['cache_on'] = FALSE;
$db['expressionengine']['autoinit'] = FALSE;
$db['expressionengine']['char_set'] = 'utf8';
$db['expressionengine']['dbcollat'] = 'utf8_general_ci';
$db['expressionengine']['cachedir'] = '/home/volgramc/public_html/adorama.volgram.com/system/expressionengine/cache/db_cache/';


if (@$_SERVER['env'] == 'production'){
    $db['expressionengine']['hostname'] = '107.167.187.132';
    $db['expressionengine']['username'] = 'root';
    $db['expressionengine']['password'] = 'vardion88';
    $db['expressionengine']['database'] = 'adorama';    
}

/* End of file database.php */
/* Location: ./system/expressionengine/config/database.php */