<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Division;
use App\Models\Operation;
use App\Models\OperationFlow;
use App\Models\Machine;
use App\Models\MachineOperation;
use App\Models\POInternal;
use App\Models\POOperation;
use App\Models\Batch;
use App\Models\BatchOperation;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123')
        ]);
        // ============================================
        // 2. DIVISIONS
        // ============================================
        $divisions = [
            ['name' => 'Wax', 'code' => 'WAX', 'description' => 'Wax Injection Division'],
            ['name' => 'Machining', 'code' => 'MCH', 'description' => 'CNC Machining Division'],
            ['name' => 'Assembling', 'code' => 'ASM', 'description' => 'Assembly Division'],
            ['name' => 'Packing', 'code' => 'PCK', 'description' => 'Packing Division'],
            ['name' => 'Quality Control', 'code' => 'QC', 'description' => 'Quality Control Division'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }

        // ============================================
        // 3. USERS
        // ============================================
        // $users = [
        //     // Admin
        //     [
        //         'name' => 'Admin User',
        //         'email' => 'admin@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'admin')->first()->id,
        //         'division_id' => null,
        //         'is_active' => true,
        //     ],
        //     // PPC
        //     [
        //         'name' => 'PPC Staff',
        //         'email' => 'ppc@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'ppc')->first()->id,
        //         'division_id' => null,
        //         'is_active' => true,
        //     ],
        //     // Wax Division
        //     [
        //         'name' => 'Operator Wax',
        //         'email' => 'operator.wax@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'operator')->first()->id,
        //         'division_id' => Division::where('code', 'WAX')->first()->id,
        //         'is_active' => true,
        //     ],
        //     [
        //         'name' => 'Supervisor Wax',
        //         'email' => 'supervisor.wax@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'supervisor')->first()->id,
        //         'division_id' => Division::where('code', 'WAX')->first()->id,
        //         'is_active' => true,
        //     ],
        //     // Machining Division
        //     [
        //         'name' => 'Operator Machining',
        //         'email' => 'operator.machining@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'operator')->first()->id,
        //         'division_id' => Division::where('code', 'MCH')->first()->id,
        //         'is_active' => true,
        //     ],
        //     [
        //         'name' => 'Supervisor Machining',
        //         'email' => 'supervisor.machining@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'supervisor')->first()->id,
        //         'division_id' => Division::where('code', 'MCH')->first()->id,
        //         'is_active' => true,
        //     ],
        //     // Assembling Division
        //     [
        //         'name' => 'Operator Assembling',
        //         'email' => 'operator.assembling@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'operator')->first()->id,
        //         'division_id' => Division::where('code', 'ASM')->first()->id,
        //         'is_active' => true,
        //     ],
        //     [
        //         'name' => 'QC Assembling',
        //         'email' => 'qc.assembling@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'qc')->first()->id,
        //         'division_id' => Division::where('code', 'ASM')->first()->id,
        //         'is_active' => true,
        //     ],
        //     // Packing Division
        //     [
        //         'name' => 'Operator Packing',
        //         'email' => 'operator.packing@example.com',
        //         'password' => Hash::make('password'),
        //         'role_id' => Role::where('name', 'operator')->first()->id,
        //         'division_id' => Division::where('code', 'PCK')->first()->id,
        //         'is_active' => true,
        //     ],
        // ];

        // foreach ($users as $user) {
        //     User::create($user);
        // }

        // ============================================
        // 4. OPERATIONS
        // ============================================
        $operations = [
            [
                'code' => 'OP-WAX-01',
                'name' => 'Wax Injection',
                'division_id' => Division::where('code', 'WAX')->first()->id,
                'estimated_duration_minutes' => 60,
                'requires_qc_before' => false,
                'requires_qc_after' => true,
                'sequence_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'OP-MCH-01',
                'name' => 'CNC Milling',
                'division_id' => Division::where('code', 'MCH')->first()->id,
                'estimated_duration_minutes' => 90,
                'requires_qc_before' => true,
                'requires_qc_after' => true,
                'sequence_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'OP-MCH-02',
                'name' => 'Drilling',
                'division_id' => Division::where('code', 'MCH')->first()->id,
                'estimated_duration_minutes' => 45,
                'requires_qc_before' => false,
                'requires_qc_after' => true,
                'sequence_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'OP-ASM-01',
                'name' => 'Assembly',
                'division_id' => Division::where('code', 'ASM')->first()->id,
                'estimated_duration_minutes' => 120,
                'requires_qc_before' => true,
                'requires_qc_after' => true,
                'sequence_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'OP-PCK-01',
                'name' => 'Final Packing',
                'division_id' => Division::where('code', 'PCK')->first()->id,
                'estimated_duration_minutes' => 30,
                'requires_qc_before' => false,
                'requires_qc_after' => true,
                'sequence_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($operations as $operation) {
            Operation::create($operation);
        }

        // ============================================
        // 5. OPERATION FLOWS
        // ============================================
        $flows = [
            ['from_operation_id' => null, 'to_operation_id' => 1, 'sequence_order' => 1], // Start → Wax
            ['from_operation_id' => 1, 'to_operation_id' => 2, 'sequence_order' => 2], // Wax → CNC Milling
            ['from_operation_id' => 2, 'to_operation_id' => 3, 'sequence_order' => 3], // CNC Milling → Drilling
            ['from_operation_id' => 3, 'to_operation_id' => 4, 'sequence_order' => 4], // Drilling → Assembly
            ['from_operation_id' => 4, 'to_operation_id' => 5, 'sequence_order' => 5], // Assembly → Packing
        ];

        foreach ($flows as $flow) {
            OperationFlow::create($flow);
        }

        // ============================================
        // 6. MACHINES
        // ============================================
        $machines = [
            // Wax Machines
            [
                'code' => 'MCH-WAX-01',
                'name' => 'Wax Injector A',
                'division_id' => Division::where('code', 'WAX')->first()->id,
                'machine_type' => 'Injection',
                'status' => 'available',
                'max_concurrent_operations' => 1,
                'current_operations' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'MCH-WAX-02',
                'name' => 'Wax Injector B',
                'division_id' => Division::where('code', 'WAX')->first()->id,
                'machine_type' => 'Injection',
                'status' => 'available',
                'max_concurrent_operations' => 1,
                'current_operations' => 0,
                'is_active' => true,
            ],
            // Machining Machines
            [
                'code' => 'MCH-CNC-01',
                'name' => 'CNC Machine 1',
                'division_id' => Division::where('code', 'MCH')->first()->id,
                'machine_type' => 'CNC',
                'status' => 'available',
                'max_concurrent_operations' => 1,
                'current_operations' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'MCH-CNC-02',
                'name' => 'CNC Machine 2',
                'division_id' => Division::where('code', 'MCH')->first()->id,
                'machine_type' => 'CNC',
                'status' => 'available',
                'max_concurrent_operations' => 1,
                'current_operations' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'MCH-DRILL-01',
                'name' => 'Drilling Machine 1',
                'division_id' => Division::where('code', 'MCH')->first()->id,
                'machine_type' => 'Drill',
                'status' => 'available',
                'max_concurrent_operations' => 2,
                'current_operations' => 0,
                'is_active' => true,
            ],
            // Assembly
            [
                'code' => 'MCH-ASM-01',
                'name' => 'Assembly Station 1',
                'division_id' => Division::where('code', 'ASM')->first()->id,
                'machine_type' => 'Manual',
                'status' => 'available',
                'max_concurrent_operations' => 1,
                'current_operations' => 0,
                'is_active' => true,
            ],
            // Packing
            [
                'code' => 'MCH-PCK-01',
                'name' => 'Packing Station 1',
                'division_id' => Division::where('code', 'PCK')->first()->id,
                'machine_type' => 'Manual',
                'status' => 'available',
                'max_concurrent_operations' => 1,
                'current_operations' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($machines as $machine) {
            Machine::create($machine);
        }

        // ============================================
        // 7. MACHINE OPERATIONS (Assign machines to operations)
        // ============================================
        $machineOperations = [
            // Wax Injection
            ['machine_id' => 1, 'operation_id' => 1, 'estimated_duration_minutes' => 55, 'setup_time_minutes' => 5, 'is_default' => true, 'is_active' => true],
            ['machine_id' => 2, 'operation_id' => 1, 'estimated_duration_minutes' => 65, 'setup_time_minutes' => 5, 'is_default' => false, 'is_active' => true],
            // CNC Milling
            ['machine_id' => 3, 'operation_id' => 2, 'estimated_duration_minutes' => 85, 'setup_time_minutes' => 10, 'is_default' => true, 'is_active' => true],
            ['machine_id' => 4, 'operation_id' => 2, 'estimated_duration_minutes' => 95, 'setup_time_minutes' => 10, 'is_default' => false, 'is_active' => true],
            // Drilling
            ['machine_id' => 5, 'operation_id' => 3, 'estimated_duration_minutes' => 40, 'setup_time_minutes' => 5, 'is_default' => true, 'is_active' => true],
            // Assembly
            ['machine_id' => 6, 'operation_id' => 4, 'estimated_duration_minutes' => 120, 'setup_time_minutes' => 0, 'is_default' => true, 'is_active' => true],
            // Packing
            ['machine_id' => 7, 'operation_id' => 5, 'estimated_duration_minutes' => 30, 'setup_time_minutes' => 0, 'is_default' => true, 'is_active' => true],
        ];

        foreach ($machineOperations as $mo) {
            MachineOperation::create($mo);
        }

        // ============================================
        // 8. PO INTERNAL
        // ============================================
        $poInternal = POInternal::create([
            'po_number' => 'PO-2024-001',
            'customer_name' => 'PT Maju Jaya',
            'product_description' => 'Custom Metal Parts - Type A',
            'quantity' => 100,
            'due_date' => now()->addDays(14),
            'status' => 'confirmed',
            'created_by' => User::where('email', 'admin@example.com')->first()->id,
        ]);

        // ============================================
        // 9. PO OPERATIONS
        // ============================================
        $poOperations = [
            ['po_internal_id' => $poInternal->id, 'operation_id' => 1, 'estimated_duration_minutes' => 60, 'sequence_order' => 1],
            ['po_internal_id' => $poInternal->id, 'operation_id' => 2, 'estimated_duration_minutes' => 90, 'sequence_order' => 2],
            ['po_internal_id' => $poInternal->id, 'operation_id' => 3, 'estimated_duration_minutes' => 45, 'sequence_order' => 3],
            ['po_internal_id' => $poInternal->id, 'operation_id' => 4, 'estimated_duration_minutes' => 120, 'sequence_order' => 4],
            ['po_internal_id' => $poInternal->id, 'operation_id' => 5, 'estimated_duration_minutes' => 30, 'sequence_order' => 5],
        ];

        foreach ($poOperations as $poOp) {
            POOperation::create($poOp);
        }

        // ============================================
        // 10. BATCH
        // ============================================
        $batch = Batch::create([
            'batch_number' => 'BATCH-' . date('Ymd') . '-0001',
            'po_internal_id' => $poInternal->id,
            'quantity' => 50,
            'priority' => 2, // High priority
            'is_rush_order' => false,
            'status' => 'released', // Ready untuk production
            'current_operation_id' => null,
            'estimated_completion_at' => now()->addMinutes(345), // Total: 60+90+45+120+30 = 345 menit
            'actual_completion_at' => null,
            'created_by' => User::where('email', 'admin@example.com')->first()->id,
            'notes' => 'First batch of 50 units from PO-2024-001',
        ]);

        // ============================================
        // 11. BATCH OPERATIONS (dengan estimasi waktu)
        // ============================================
        $startTime = now()->addHours(1); // Mulai 1 jam dari sekarang
        
        $batchOperations = [
            [
                'batch_id' => $batch->id,
                'operation_id' => 1,
                'machine_id' => null,
                'sequence_order' => 1,
                'estimated_duration_minutes' => 60,
                'status' => 'ready', // Siap dimulai
                'estimated_start_at' => $startTime,
                'estimated_completion_at' => $startTime->copy()->addMinutes(60),
            ],
            [
                'batch_id' => $batch->id,
                'operation_id' => 2,
                'machine_id' => null,
                'sequence_order' => 2,
                'estimated_duration_minutes' => 90,
                'status' => 'pending',
                'estimated_start_at' => $startTime->copy()->addMinutes(60),
                'estimated_completion_at' => $startTime->copy()->addMinutes(150),
            ],
            [
                'batch_id' => $batch->id,
                'operation_id' => 3,
                'machine_id' => null,
                'sequence_order' => 3,
                'estimated_duration_minutes' => 45,
                'status' => 'pending',
                'estimated_start_at' => $startTime->copy()->addMinutes(150),
                'estimated_completion_at' => $startTime->copy()->addMinutes(195),
            ],
            [
                'batch_id' => $batch->id,
                'operation_id' => 4,
                'machine_id' => null,
                'sequence_order' => 4,
                'estimated_duration_minutes' => 120,
                'status' => 'pending',
                'estimated_start_at' => $startTime->copy()->addMinutes(195),
                'estimated_completion_at' => $startTime->copy()->addMinutes(315),
            ],
            [
                'batch_id' => $batch->id,
                'operation_id' => 5,
                'machine_id' => null,
                'sequence_order' => 5,
                'estimated_duration_minutes' => 30,
                'status' => 'pending',
                'estimated_start_at' => $startTime->copy()->addMinutes(315),
                'estimated_completion_at' => $startTime->copy()->addMinutes(345),
            ],
        ];

        foreach ($batchOperations as $batchOp) {
            BatchOperation::create($batchOp);
        }

        // Update batch current operation
        $batch->update(['current_operation_id' => 1]);

        $this->command->info('✅ Seeder completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Created:');
        $this->command->info('   - 5 Roles (admin, ppc, operator, qc, supervisor)');
        $this->command->info('   - 5 Divisions (Wax, Machining, Assembling, Packing, QC)');
        $this->command->info('   - 9 Users (with password: password)');
        $this->command->info('   - 5 Operations (Wax → CNC → Drill → Assembly → Packing)');
        $this->command->info('   - 7 Machines (assigned to operations)');
        $this->command->info('   - 1 PO Internal (PO-2024-001) - 100 units');
        $this->command->info('   - 1 Batch (BATCH-' . date('Ymd') . '-0001) - 50 units');
        $this->command->info('   - 5 Batch Operations (with timeline)');
        $this->command->info('');
        $this->command->info('🔐 Login credentials:');
        $this->command->info('   Admin: admin@example.com / password');
        $this->command->info('   PPC: ppc@example.com / password');
        $this->command->info('   Operator Wax: operator.wax@example.com / password');
        $this->command->info('   Operator Machining: operator.machining@example.com / password');
        $this->command->info('   QC: qc.assembling@example.com / password');
        $this->command->info('');
        $this->command->info('⏱️  Batch Timeline:');
        $this->command->info('   Start: ' . $startTime->format('d M Y H:i'));
        $this->command->info('   Est. Completion: ' . $startTime->copy()->addMinutes(345)->format('d M Y H:i'));
        $this->command->info('   Total Duration: 345 minutes (5h 45m)');
    }
}
