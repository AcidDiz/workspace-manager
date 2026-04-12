<?php

namespace Database\Seeders;

use App\Enums\WorkshopRegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Seeder;

class AcademyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Academy Admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        $employee = User::factory()->create([
            'name' => 'Demo Employee',
            'email' => 'employee@example.com',
        ]);
        $employee->assignRole('employee');

        $w1 = Workshop::create([
            'title' => 'Laravel in practice',
            'description' => 'Routing, requests, and testing essentials for the workshop app.',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(4),
            'capacity' => 20,
            'created_by' => $admin->id,
        ]);

        $w2 = Workshop::create([
            'title' => 'Vue component design',
            'description' => 'Patterns for Inertia pages and reusable UI pieces.',
            'starts_at' => now()->addWeeks(2),
            'ends_at' => now()->addWeeks(2)->addHours(3),
            'capacity' => 12,
            'created_by' => $admin->id,
        ]);

        $w3 = Workshop::create([
            'title' => 'Database modeling',
            'description' => 'Relationships, constraints, and realistic seed data.',
            'starts_at' => now()->addWeeks(3),
            'ends_at' => now()->addWeeks(3)->addHours(5),
            'capacity' => 15,
            'created_by' => $admin->id,
        ]);

        WorkshopRegistration::create([
            'workshop_id' => $w1->id,
            'user_id' => $employee->id,
            'status' => WorkshopRegistrationStatus::Confirmed,
        ]);

        WorkshopRegistration::create([
            'workshop_id' => $w2->id,
            'user_id' => $employee->id,
            'status' => WorkshopRegistrationStatus::Confirmed,
        ]);

        WorkshopRegistration::create([
            'workshop_id' => $w3->id,
            'user_id' => $employee->id,
            'status' => WorkshopRegistrationStatus::WaitingList,
        ]);
    }
}
