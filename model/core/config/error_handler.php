<?php

register_shutdown_function(function ()
{
    $last_error = error_get_last();
    if (!empty($last_error) &&
        $last_error['type'] & (E_ERROR | E_COMPILE_ERROR | E_PARSE | E_CORE_ERROR | E_USER_ERROR)
    )
    {
        require_once(dirname(__FILE__) . '/../../../view/error.php');
        exit(1);
    }
});