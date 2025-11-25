<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low Balance Threshold
    |--------------------------------------------------------------------------
    |
    | This value determines the minimum cash balance before a low balance
    | alert is triggered on the dashboard. When the current balance falls
    | below this threshold, users will see a warning notification.
    |
    */

    'low_balance_threshold' => env('LOW_BALANCE_THRESHOLD', 1000.00),

];
