<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)) {
    $db = DB::getInstance();
    include "plugin_info.php";

    //all actions should be performed here.
    $check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?", array($plugin_name))->count();
    if ($check > 0) {
        err($plugin_name.' has already been installed!');
    } else {
        $db->query("CREATE TABLE IF NOT EXISTS `us_bf_failed_logins` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`ip_address` int(11) unsigned DEFAULT NULL,
		`attempted_at` datetime NOT NULL,
		PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;");
    $db->query("CREATE TABLE IF NOT EXISTS `us_bf_limits` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `attempts` int(4) NOT NULL,
        `action` varchar(10) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;");
    if ($db->query("SELECT * FROM us_bf_limits")->count() < 4) {
        $db->query("DELETE FROM us_bf_limits");
        $db->query("INSERT INTO `us_bf_limits` (`id`, `attempts`, `action`) VALUES
        (1, 5, '10'),
        (2, 10, '60'),
        (3, 30, '600'),
        (4, 100, 'ban');");
    }
      
        $db->query("ALTER TABLE settings ADD COLUMN bf_proxy VARCHAR(100) NOT NULL DEFAULT 'REMOTE_ADDR'");
        $db->query("ALTER TABLE settings ADD COLUMN bf_clear_failed_logins tinyint(1) NOT NULL DEFAULT 1");
        $db->query("ALTER TABLE settings ADD COLUMN bf_ban tinyint(1) NOT NULL DEFAULT 0");
        $db->query("ALTER TABLE settings ADD COLUMN bf_time_frame INT(10) NOT NULL DEFAULT 60");
        $fields = array(
             'plugin'=>$plugin_name,
             'status'=>'installed',
        );
        $db->insert('us_plugins', $fields);
        if (!$db->error()) {
            err($plugin_name.' installed');
            logger($user->data()->id, "USPlugins", $plugin_name." installed");
        } else {
            err($plugin_name.' was not installed');
            logger($user->data()->id, "USPlugins", "Failed to to install plugin, Error: ".$db->errorString());
        }
    }

    //do you want to inject your plugin in the middle of core UserSpice pages?
    $hooks = [];

    //The format is $hooks['userspicepage.php']['position'] = path to filename to include
    //Note you can include the same filename on multiple pages if that makes sense;
    //postion options are post,body,form,bottom
    //See documentation for more information
    $hooks['login.php']['post'] = 'hooks/loginpost.php';
    $hooks['loginFail']['body'] = 'hooks/loginfailbody.php';
    registerHooks($hooks, $plugin_name);
} //do not perform actions outside of this statement