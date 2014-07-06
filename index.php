<?php

// inspired by http://youtu.be/e2OnJfhkLxU

require __DIR__.'/vendor/autoload.php';

define('STEAM_API_KEY', '0123456789ABCDEFGHIJKLMNOPQRSTUV');
$openId = new \LightOpenID('localhost');

session_start();

if (isset($_GET['logout'])) {
    session_destroy();

    header('Location: /');
    die;
}

if (!$openId->mode) {
    if (isset($_GET['login'])) {
        $openId->identity = 'http://steamcommunity.com/openid';

        header(sprintf('Location: %s', $openId->authUrl()));
        die;
    }

    if (!isset($_SESSION['steam_url'])) {

        echo '<a href="?login">Login using Steam</a>.';
        die;
    }
} elseif ($openId->mode == 'cancel') {

    echo 'User has canceled the authentication process. <a href="?login">Try again</a>.';
    die;
} else {

    if (!isset($_SESSION['steam_url'])) {
        $base_steam_url = 'http://steamcommunity.com/openid/id/';

        $_SESSION['steam_url'] = $openId->validate() ? $openId->identity : null;
        $_SESSION['steam_id'] = str_replace($base_steam_url, '', $_SESSION['steam_url']);

        if (!isset($_SESSION['steam_profile'])) {
            $profile_json_url = sprintf(
                'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s',
                STEAM_API_KEY,
                $_SESSION['steam_id']
            );
            $profile_json = json_decode(file_get_contents($profile_json_url));

            $_SESSION['steam_details'] = $profile_json->response->players[0];
        }
    }

}

var_dump($_SESSION);
echo '<a href="?logout">Logout</a>';
