<?php
return [
    'rim_events_enabled' => true,
    'kri' => [
        'alert_statuses' => ['alert','breach'],
        'daily_import_path' => storage_path('app/tpr/kri-inbox'), // CSV files
    ],
    'sla' => [
        'breach_threshold_status' => 'breach'
    ],
    'assessments' => [
        'overdue_days' => 7
    ]
];
