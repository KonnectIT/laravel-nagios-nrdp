<?php

return [

    'url' => env('NAGIOS_NRDP_SERVER_URL', ''),

    'host' => env('NAGIOS_NRDP_HOST', gethostname()),

    'token' => env('NAGIOS_NRDP_TOKEN', ''),

];
