<?php if (count(get_included_files()) ==1) {
    die();
} //Direct Access Not Permitted?>

<?php
global $abs_us_root;
global $us_url_root;
include_once $abs_us_root . $us_url_root . 'usersc/plugins/bfblock/assets/BruteForceBlock.php';

$response = bangingheads\BruteForceBlock::getLoginStatus();

switch ($response['status']) {
    case 'safe':
        //Do nothing
        break;
    case 'delay':
        //time delay required before next login
        $delay = $response['message'];
        Redirect::to("login.php?err=Login+attempts+exceeded.+Please+try+again+in+$delay+seconds");
        break;
    case 'error':
        //error occured. get message
        logger(0, "Brute Force Block", "Error occured with getting failed login attempts." + $response['message']);
        Redirect::to("login.php?err=An+unknown+error+has+occured+with+your+login.+Please+try+again.");
        break;
    
}

?>