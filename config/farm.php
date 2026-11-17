<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Starter data for new accounts
    |--------------------------------------------------------------------------
    |
    | When enabled, every newly registered account receives a set of empty
    | cages so the app is usable immediately. Users can rename or delete them.
    | Set REGISTRATION_STARTER_DATA=false to start with a completely empty
    | piggery instead.
    |
    */

    'starter_data' => (bool) env('REGISTRATION_STARTER_DATA', true),

    'starter_cages' => [
        ['cage_number' => 'G1', 'type' => 'gestation', 'capacity' => 1, 'location' => 'Barn A'],
        ['cage_number' => 'G2', 'type' => 'gestation', 'capacity' => 1, 'location' => 'Barn A'],
        ['cage_number' => 'G3', 'type' => 'gestation', 'capacity' => 1, 'location' => 'Barn A'],
        ['cage_number' => 'G4', 'type' => 'gestation', 'capacity' => 1, 'location' => 'Barn A'],
        ['cage_number' => 'F1', 'type' => 'farrowing', 'capacity' => 1, 'location' => 'Barn B'],
        ['cage_number' => 'F2', 'type' => 'farrowing', 'capacity' => 1, 'location' => 'Barn B'],
        ['cage_number' => 'F3', 'type' => 'farrowing', 'capacity' => 1, 'location' => 'Barn B'],
        ['cage_number' => 'B1', 'type' => 'boar', 'capacity' => 1, 'location' => 'Barn C'],
        ['cage_number' => 'B2', 'type' => 'boar', 'capacity' => 1, 'location' => 'Barn C'],
        ['cage_number' => 'W1', 'type' => 'weaning', 'capacity' => 12, 'location' => 'Open shed'],
        ['cage_number' => 'W2', 'type' => 'weaning', 'capacity' => 12, 'location' => 'Open shed'],
        ['cage_number' => 'Q1', 'type' => 'quarantine', 'capacity' => 2, 'location' => 'Open shed'],
    ],

];
