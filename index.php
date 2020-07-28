<?php
require("config.php");
$wpConfig['view']['config']['template_dir'] = APP_PATH.'/template/'.$wpConfig['ext']['view_themes'];
require(WIND_PATH."/system.php");
systemRun();