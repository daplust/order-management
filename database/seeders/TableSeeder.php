<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['number' => 'T1',  'capacity' => 2,  'description' => 'Window side table for two',                 'is_available' => true],
            ['number' => 'T2',  'capacity' => 4,  'description' => 'Center table for family',                    'is_available' => true],
            ['number' => 'T3',  'capacity' => 6,  'description' => 'Large table for groups',                     'is_available' => true],
            ['number' => 'T4',  'capacity' => 2,  'description' => 'Quiet corner table for two',                 'is_available' => true],
            ['number' => 'T5',  'capacity' => 8,  'description' => 'Large group table with booth seating',       'is_available' => true],
            ['number' => 'T6',  'capacity' => 2,  'description' => 'Patio table for two',                        'is_available' => true],
            ['number' => 'T7',  'capacity' => 4,  'description' => 'High-top table for four',                    'is_available' => true],
            ['number' => 'T8',  'capacity' => 2,  'description' => 'Bar-side table for two',                     'is_available' => true],
            ['number' => 'T9',  'capacity' => 4,  'description' => 'Near kitchen table for four',                'is_available' => true],
            ['number' => 'T10', 'capacity' => 6,  'description' => 'Family table near window',                   'is_available' => true],
            ['number' => 'T11', 'capacity' => 2,  'description' => 'Cozy booth for two',                         'is_available' => true],
            ['number' => 'T12', 'capacity' => 4,  'description' => 'Booth for four',                             'is_available' => true],
            ['number' => 'T13', 'capacity' => 6,  'description' => 'Communal table for larger groups',           'is_available' => true],
            ['number' => 'T14', 'capacity' => 8,  'description' => 'Banquet table for parties',                  'is_available' => true],
            ['number' => 'T15', 'capacity' => 10, 'description' => 'Private long table for events',              'is_available' => true],
        ];

        foreach ($tables as $table) {
            Table::create($table);
        }
    }
}