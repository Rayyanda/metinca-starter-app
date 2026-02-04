<?php

namespace Database\Seeders;

use App\Modules\Repair\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        $machines = [
            // Lini Produksi A
            [
                'code' => 'MCH-001',
                'name' => 'Mesin CNC Milling A1 (HAAS VF-2SS)',
                'department' => 'Produksi',
                'location' => 'Gedung A - Lantai 1 - Mesin',
            ],
            [
                'code' => 'MCH-002',
                'name' => 'Mesin CNC Bubut A2 (DMG MORI NLX-2500)',
                'department' => 'Produksi',
                'location' => 'Gedung A - Lantai 1 - Mesin',
            ],
            [
                'code' => 'MCH-003',
                'name' => 'Mesin Injection Molding (ENGEL E-MAX 440)',
                'department' => 'Produksi',
                'location' => 'Gedung A - Lantai 2 - Molding',
            ],

            // Lini Produksi B
            [
                'code' => 'MCH-004',
                'name' => 'Mesin Press B1 (AMADA HFE-M2-5020)',
                'department' => 'Produksi',
                'location' => 'Gedung B - Lantai 1 - Stamping',
            ],
            [
                'code' => 'MCH-005',
                'name' => 'Robot Welding B2 (FANUC ARC Mate 120iD)',
                'department' => 'Produksi',
                'location' => 'Gedung B - Lantai 1 - Perakitan',
            ],

            // Lini Perakitan
            [
                'code' => 'MCH-006',
                'name' => 'Sistem Conveyor Belt 1 (DORNER 2200 Series)',
                'department' => 'Perakitan',
                'location' => 'Gedung C - Lantai 1 - Lini Utama',
            ],
            [
                'code' => 'MCH-007',
                'name' => 'Mesin Baut Otomatis (WEBER DS 50)',
                'department' => 'Perakitan',
                'location' => 'Gedung C - Lantai 1 - Lini Utama',
            ],
            [
                'code' => 'MCH-008',
                'name' => 'Robot Pick and Place (ABB IRB 360)',
                'department' => 'Perakitan',
                'location' => 'Gedung C - Lantai 2 - Pengemasan',
            ],

            // Kontrol Kualitas
            [
                'code' => 'MCH-009',
                'name' => 'Mesin Inspeksi CMM (ZEISS ACCURA II)',
                'department' => 'Kontrol Kualitas',
                'location' => 'Gedung D - Lantai 1 - Inspeksi',
            ],
            [
                'code' => 'MCH-010',
                'name' => 'Sistem Inspeksi X-Ray (YXLON FF35 CT)',
                'department' => 'Kontrol Kualitas',
                'location' => 'Gedung D - Lantai 1 - Inspeksi',
            ],

            // Pemeliharaan
            [
                'code' => 'MCH-011',
                'name' => 'Mesin Press Hidrolik (ENERPAC IPE-5075)',
                'department' => 'Pemeliharaan',
                'location' => 'Gedung E - Lantai 1 - Bengkel',
            ],
            [
                'code' => 'MCH-012',
                'name' => 'Mesin Gerinda Permukaan (OKAMOTO ACC-64DX)',
                'department' => 'Pemeliharaan',
                'location' => 'Gedung E - Lantai 1 - Bengkel',
            ],

            // Pengemasan
            [
                'code' => 'MCH-013',
                'name' => 'Mesin Shrink Wrap (SMIPACK S-560)',
                'department' => 'Pengemasan',
                'location' => 'Gedung F - Lantai 1 - Pak Akhir',
            ],
            [
                'code' => 'MCH-014',
                'name' => 'Mesin Labeling (HERMA H-400)',
                'department' => 'Pengemasan',
                'location' => 'Gedung F - Lantai 1 - Pak Akhir',
            ],
            [
                'code' => 'MCH-015',
                'name' => 'Robot Palletizer (KUKA KR 700 PA)',
                'department' => 'Pengemasan',
                'location' => 'Gedung F - Lantai 2 - Gudang',
            ],
        ];

        foreach ($machines as $machineData) {
            Machine::firstOrCreate(
                ['code' => $machineData['code']],
                $machineData
            );
        }

        $this->command->info('Berhasil membuat ' . count($machines) . ' mesin!');
    }
}
