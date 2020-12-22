<?php

namespace bangingheads;

class BruteForceBlock
{
    private static function getThrottleLimits()
    {
        global $db;
        $query = $db->query("SELECT * FROM us_bf_limits ORDER BY attempts ASC")->results();
        $throttle = array();
        foreach ($query as $result) {
            $throttle[$result->attempts] = $result->action;
        }
        return $throttle;
    }


    public static function getIPAddress()
    {
        global $db;
        $query = $db->query("SELECT bf_proxy FROM settings")->first();
        return $_SERVER[$query->bf_proxy];
    }
    
    public static function addFailedLoginAttempt($user_id)
    {
        global $db;
        $ip_address=self::getIPAddress();
        $db->query('INSERT INTO us_bf_failed_logins SET user_id = ?, ip_address = INET_ATON(?), attempted_at = NOW()', [$user_id, $ip_address]);
        if (!$db->error()) {
            return true;
        } else {
            return $db->errorString();
        }
    }
    public static function getLoginStatus()
    {
        global $db;
        $ip_address=self::getIPAddress();
        
        $response_array = array(
            'status' => 'safe',
            'message' => null
        );
        
        $stmt = $db->query('SELECT MAX(attempted_at) AS attempted_at FROM us_bf_failed_logins WHERE ip_address = INET_ATON(?)', [$ip_address]);
        if ($db->error()) {
            $response_array['status'] = 'error';
            $response_array['message'] = $db->errorString();
            return $response_array;
        }
        $latest_failed_logins = $stmt->count();
        $row = $stmt->first(true);
        $latest_failed_attempt_datetime = (int) date('U', strtotime($row['attempted_at']));

        $settings = $db->query("SELECT * FROM settings")->first();
        $throttle_settings = self::getThrottleLimits();

        reset($throttle_settings);
        $first_throttle_limit = key($throttle_settings);
        $time_frame = $settings->bf_time_frame;
        $get_number = $db->query('SELECT * FROM us_bf_failed_logins WHERE ip_address = INET_ATON(?) AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)', [$ip_address, $time_frame]);
        $number_recent_failed = $get_number->count();
        krsort($throttle_settings);
        if ($number_recent_failed >= $first_throttle_limit) {
            foreach ($throttle_settings as $attempts => $delay) {
                if ($number_recent_failed > $attempts) {
                    if (is_numeric($delay)) {
                        $next_login_minimum_time = $latest_failed_attempt_datetime + $delay;
                        if (time() < $next_login_minimum_time) {
                            $remaining_delay = $next_login_minimum_time - time();
                            $response_array['status'] = 'delay';
                            $response_array['message'] = $remaining_delay;
                        } else {
                            $response_array['status'] = 'safe';
                        }
                    } else {
                        if ($delay == "ban") {
                            if ($db->query("SELECT bf_ban FROM settings")->first()->bf_ban == 1){
                                $db->insert("us_ip_blacklist", ["ip"=>$ip_address,"last_user"=>0,"reason"=>0]);
                            }
                        } else {
                            $response_array['status'] = 'captcha'; //Potentially for another release we can respond captcha but captcha is handled in core right now.
                        }
                    }
                    break;
                }
            }
        }
        if ($settings->bf_clear_failed_logins) {
            $stmt = $db->query('DELETE from us_bf_failed_logins WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)', [($time_frame * 2)]);
            if ($db->error()) {
                $response_array['status'] = 'error';
                $response_array['message'] = $db->errorString();
            }
        }
        return $response_array;
    }
    
    public static function clearDatabase()
    {
        global $db;
        $db->query('DELETE from us_bf_failed_logins');
        return true;
    }
}