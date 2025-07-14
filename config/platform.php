<?php

return [

    'cache_control_max_age' => (int) env('CACHE_CONTROL_MAX_AGE', 86400), // 1 day

    'api_rate_limit' => (int) env('API_RATE_LIMIT', 120), // requests per minute
];
