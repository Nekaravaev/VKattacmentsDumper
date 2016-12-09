<?php

class Dumper
{
}
/*
* Use https://vk.cc/5WLbhs for access_token obtain
* See config.json.example for structure
*/
$config = json_decode(file_get_contents('config.json'), true);
if (isset($config) && !empty($config)) {
    list($token, $userId) = array_values($config);
} else {
    die('Can\'t obtain token and userId. Please, fill config.json');
}

/**
 * Recursively get photos with offset.
 *
 * @param $start initial step to start downloading
 * @param $token access_token for VK API access
 * @param $userId VK user id which attachments to dump
 *
 * @return status of downloading
 */
function getPhotos($start, $token, $userId)
{
    $response = useCurl("https://api.vk.com/method/messages.getHistoryAttachments?peer_id=$userId&count=200&access_token=$token&start=$start&media_type=photo");

    if (!empty($response->items)) {
        $pictures = $response->items;
        foreach ($pictures->photo as $picture) {
        }
    } else {
        return false;
    }

    return true;
}

/**
 * Search the most high resolution.
 *
 * @param object $data object with response from VK
 *
 * @return string highest resolution url
 */
function getHighResolution($data)
{
}

/**
 * Use curl lib for receive data.
 *
 * @param string $url Host with get params to connect
 *
 * @return object decoded json object
 */
function useCurl($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
}
