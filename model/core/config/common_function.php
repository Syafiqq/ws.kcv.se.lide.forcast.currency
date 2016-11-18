<?php
/**
 * This <ws.kcv.se.lide.forcast.currency> project created by :
 * Name         : syafiq
 * Date / Time  : 27 October 2016, 1:28 PM.
 * Email        : syafiq.rezpector@gmail.com
 * Github       : syafiqq
 */

function base_url($path)
{
    return "http://{$_SERVER['SERVER_NAME']}/$path";
}

function site_url($path)
{
    return "http://{$_SERVER['SERVER_NAME']}/index.php/$path";
}
