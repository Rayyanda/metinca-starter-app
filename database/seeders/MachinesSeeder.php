<?php

namespace Database\Seeders;

use App\Modules\Repair\Models\Machine;
use Illuminate\Database\Seeder;

class MachinesSeeder extends Seeder
{
    public function run(): void
    {
        $machines = [
            ['code' => 'CNC-P1', 'name' => 'CNC Machine P1', 'department' => 'Production', 'location' => 'Building A'],
            ['code' => 'CNC-P2', 'name' => 'CNC Machine P2', 'department' => 'Production', 'location' => 'Building A'],
            ['code' => 'FURN-01', 'name' => 'Furnace 01', 'department' => 'Casting', 'location' => 'Building B'],
            ['code' => 'MILL-01', 'name' => 'Milling Machine 01', 'department' => 'Machining', 'location' => 'Building C'],
            ['code' => 'LATHE-01', 'name' => 'Lathe Machine 01', 'department' => 'Machining', 'location' => 'Building C'],
            ['code' => 'PRESS-01', 'name' => 'Press Machine 01', 'department' => 'Production', 'location' => 'Building A'],
        ];

        foreach ($machines as $machine) {
            Machine::firstOrCreate(['code' => $machine['code']], $machine);
        }
    }
}
