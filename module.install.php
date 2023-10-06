<?php

// Module config file
if (! file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'yapikredi.php')){
    file_put_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'yapikredi.php', "<?php \n\n require_once implode(DIRECTORY_SEPARATOR, [__DIR__, basename(__FILE__, '.php'), 'functions', 'whmcs.module.config.php']);");
}

// Module callback file
if (! file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'callback' . DIRECTORY_SEPARATOR . 'yapikredi.php')){
    file_put_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'callback' . DIRECTORY_SEPARATOR . 'yapikredi.php', "<?php \n\n require_once implode(DIRECTORY_SEPARATOR, [dirname(\$_SERVER['SCRIPT_FILENAME'],2), basename(__FILE__, '.php'), 'functions', 'whmcs.module.callback.php']);");
}