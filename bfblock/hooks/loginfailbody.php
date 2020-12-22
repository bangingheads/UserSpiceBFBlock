<?php if (count(get_included_files()) ==1) {
    die();
} //Direct Access Not Permitted?>

<?php
global $abs_us_root;
global $us_url_root;
include_once $abs_us_root . $us_url_root . 'usersc/plugins/bfblock/assets/BruteForceBlock.php';

//Add Failed login attempt to database
global $db;
$query = $db->query('SELECT id FROM users WHERE username = ? OR email = ?', [Input::get('username'), Input::get('username')]);
if ($db->count() > 0) {
    $user_id = $query->first()->id;
} else {
    $user_id = 0;
}
bangingheads\BruteForceBlock::addFailedLoginAttempt($user_id);

?>