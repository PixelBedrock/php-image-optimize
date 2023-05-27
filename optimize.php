<?php

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

//Polyfill str_contains for PHP versions < 8
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

//Block cross-origin requests (optional)
header("Cross-Origin-Resource-Policy: same-site");

//Require an image
if($_GET['url']==null){
    http_response_code(401);
    die("An image URL must be provided");
}
//Limit to GM URLs
if(!str_contains($_GET['url'],'https://img.gamemonetize.com/')){
    http_response_code(403);
    die("You are only allowed to provide GameMonetize URLs!");
}

//Wrap everything in a try-catch
try{

//Construct image
$gdimage=imagecreatefromjpeg($_GET['url']);
if (!$gdimage)
{
    $gdimage=imagecreatefromstring(file_get_contents($_GET['url']));
}

//jpeg function MAY return false if it could not construct from url, maybe bad url?
if($gdimage==false){
    http_response_code(500);
    die("Failed to optimise image: create returned false");
}

//Scale down to 256x192
$output=imagescale($gdimage,256);

//Indicate that this is an image
header("Content-Type: image/webp");

//Set up caching parameters
header("Cache-Control: public; must-revalidate; max-age=259200; stale-while-revalidate=86400; stale-if-error=86400");

//Serve the image in WEBP with 60% quality
imagewebp($output,null,60);

}catch(Exception $e){
    //Display generic error
    http_response_code(500);
    die("An error occurred while optimising the image.");
}
?>
