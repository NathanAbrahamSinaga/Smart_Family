<?php
if (extension_loaded('gd')) {
    echo "GD is installed<br>";
    
    $info = gd_info();
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    
    if (function_exists('imagewebp')) {
        echo "WebP support is available";
    } else {
        echo "WebP support is NOT available";
    }
} else {
    echo "GD is NOT installed";
}
?>