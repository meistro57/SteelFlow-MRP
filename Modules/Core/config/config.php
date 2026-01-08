<?php
// Modules/Core/config/config.php

declare(strict_types=1);

return [
    'status_pipelines' => [
        'project' => [
            'bid' => ['label' => 'Bid', 'color' => 'gray', 'next' => 'active'],
            'active' => ['label' => 'Active', 'color' => 'primary', 'next' => 'production'],
            'production' => ['label' => 'Production', 'color' => 'warning', 'next' => 'shipping'],
            'shipping' => ['label' => 'Shipping', 'color' => 'info', 'next' => 'complete'],
            'complete' => ['label' => 'Complete', 'color' => 'success', 'next' => 'archived'],
            'archived' => ['label' => 'Archived', 'color' => 'secondary'],
        ],
        'stock_item' => [
            'free' => ['label' => 'Free', 'color' => 'success'],
            'assigned' => ['label' => 'Assigned', 'color' => 'warning'],
            'committed' => ['label' => 'Committed', 'color' => 'warning'],
            'used' => ['label' => 'Used', 'color' => 'secondary'],
            'available' => ['label' => 'Available', 'color' => 'success'],
            'reserved' => ['label' => 'Reserved', 'color' => 'info'],
            'checked_out' => ['label' => 'Checked Out', 'color' => 'warning'],
            'lost' => ['label' => 'Lost', 'color' => 'danger'],
            'damaged' => ['label' => 'Damaged', 'color' => 'danger'],
        ],
    ],
];
