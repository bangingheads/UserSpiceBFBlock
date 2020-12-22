<?php if (!in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root.'users/admin.php');
} //only allow master accounts to manage plugins!?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST['plugin_bfblock'])) {
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $db->query("UPDATE settings SET bf_proxy = ?", [$_POST['bf_proxy']]);
    $db->query('DELETE FROM us_bf_limits WHERE action != "ban"');
    foreach($_POST['bf_thresh'] as $key=>$value) {
        $db->insert("us_bf_limits", ["attempts"=>$value, "action"=>$_POST['bf_delay'][$key]]);
    }
    $db->query('UPDATE us_bf_limits SET attempts = ? WHERE action = "ban"',[$_POST['bf_ban_threshold']]);
    $settings = $db->query("SELECT * FROM settings")->first();
}
$token = Token::generate();
?>
<div class="content mt-3">
    <div class="row">
        <div class="col-10 offset-1">
            <h2>Brute Force Block Settings</h2><br>
            <strong>Please note:</strong> Make sure if clients are not directly connecting to your server, change proxy
            settings or else you will block your proxy's IP. If you are not behind a proxy do not set change this setting.<br><br>
            <!-- left -->
            <div class="form-group">
                <form action="<?=$_SERVER['PHP_SELF']?>?view=plugins_config&plugin=bfblock" method="POST">
                    <input type="hidden" name="csrf" value="<?=$token?>">
                    <label for="bf_proxy">Proxy Settings</label><br>
                    <select name="bf_proxy">
                        <option value="REMOTE_ADDR" <?php if ($settings->bf_proxy=="REMOTE_ADDR") {
    echo 'selected';
}?>>None (REMOTE_ADDR)</option>
                        <option value="X-Forwarded-For" <?php if ($settings->bf_proxy=="X-Forwarded-For") {
    echo 'selected';
}?>>Proxy (X-Forwarded-For)</option>
                        <option value="HTTP_X_FORWARDED_FOR" <?php if ($settings->bf_proxy=="HTTP_X_FORWARDED_FOR") {
    echo 'selected';
}?>>Newer Proxies/Cloudflare (HTTP_X_FORWARDED_FOR)</option>
                    </select>
            </div>
            <br>
            <h3><b>Delay Settings</b></h3><br>
            This plugin uses delay to stop brute force attacks. The delay triggers after so many attempts. Here you can
            configure 3 thresholds and the amount of delay after that many attempts.
            <br><br>
            <?php
            $limits = $db->query("SELECT * FROM us_bf_limits WHERE action != 'ban' ORDER BY attempts")->results();
            foreach($limits as $limit) { ?>
            After <input type="number" name="bf_thresh[]" value="<?=$limit->attempts?>"> attempts, wait <input
                type="number" name="bf_delay[]" value="<?=$limit->action?>"> seconds.
            <br><br><?php
            }?>
            <br><br>
            <h3><b>Login Frame Interval</b><br></h3>
            By default we will automatically clear failed logins to save storage. If you would like to keep a
                permanent log you can turn this off.<br><br>
            <label class="switch switch-text switch-success">
                <input id="bf_clear_failed_logins" name="bf_clear_failed_logins" type="checkbox"
                    class="switch-input toggle" value="Yes" data-desc="Clear Failed Logins"
                    <?php if ($settings->bf_clear_failed_logins==1) {echo 'checked="true"';} ?>>
                <span data-on="Yes" data-off="No" class="switch-label"></span>
                <span class="switch-handle"></span>
            </label>
            <br><br>
            Login Frame Interval. This decides how many minutes before the login to check for failed logins. This will need to be bigger than your highest timeout.<br><br>
            <input type="number" name="bf_time_frame" value="<?=$settings->bf_time_frame?>"><br><br>
            <p>Logins will be deleted at double the interval if enabled.</p>
            <br><br>
            <h3><b>Ban User Threshold</b><br></h3>
            Turning this on will allow you to set a threshold that will permanently ban an IP address from your website.
            If you choose to use this feature we would recommend it is set to a high number of attempts.<br><br>
            <label class="switch switch-text switch-success">
                <input id="bf_ban" name="bf_ban" type="checkbox" class="switch-input toggle" value="Yes"
                    data-desc="Ban IP" <?php if ($settings->bf_ban==1) {echo 'checked="true"';} ?>>
                <span data-on="Yes" data-off="No" class="switch-label"></span>
                <span class="switch-handle"></span>
            </label><br><br>
            <?php
            $ban = $db->query("SELECT * FROM us_bf_limits WHERE action = 'ban'")->first();
            ?>
            After <input type="number" name="bf_ban_threshold" value="<?=$ban->attempts?>"> failed attempts in
            your set window, permanently ban this IP address.
            <br><br><br><br>
            <input type="submit" class="btn btn-success" name="plugin_bfblock" value="Update">
            </form>
        </div>
    </div>
</div>