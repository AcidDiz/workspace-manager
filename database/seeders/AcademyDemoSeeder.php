<?php

namespace Database\Seeders;

use App\Enums\WorkshopRegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AcademyDemoSeeder extends Seeder
{
    /** @var int Number of workshop rows produced by {@see workshopDefinitions()}. */
    public const WORKSHOP_COUNT = 37;

    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Academy Admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        $employees = $this->createEmployees();

        $definitions = $this->workshopDefinitions();
        assert(
            count($definitions) === self::WORKSHOP_COUNT,
            'WORKSHOP_COUNT must match workshopDefinitions() length.'
        );

        $workshops = collect($definitions)->map(function (array $row) use ($admin) {
            return Workshop::create([
                'title' => $row['title'],
                'description' => $row['description'],
                'starts_at' => now()->addDays($row['starts_in_days'])->setTimeFromTimeString($row['start_time'] ?? '10:00:00'),
                'ends_at' => now()->addDays($row['starts_in_days'])->setTimeFromTimeString($row['start_time'] ?? '10:00:00')
                    ->addHours($row['duration_hours']),
                'capacity' => $row['capacity'],
                'created_by' => $admin->id,
            ]);
        });

        $workshops->each(function (Workshop $workshop, int $index) use ($employees) {
            $this->seedRegistrationsForWorkshop($workshop, $index, $employees);
        });
    }

    /**
     * @return Collection<int, User>
     */
    private function createEmployees(): Collection
    {
        $primary = User::factory()->create([
            'name' => 'Demo Employee',
            'email' => 'employee@example.com',
        ]);
        $primary->assignRole('employee');

        $others = User::factory()
            ->count(14)
            ->create()
            ->each(fn (User $user) => $user->assignRole('employee'));

        return collect([$primary, ...$others->all()]);
    }

    /**
     * Deterministic catalogue: ordered by start time so the soonest workshop stays stable for tests and UI.
     *
     * @return list<array{title: string, description: string, starts_in_days: int, duration_hours: int, capacity: int, start_time?: string}>
     */
    private function workshopDefinitions(): array
    {
        return [
            ['title' => 'Laravel in practice', 'description' => 'Routing, requests, and testing essentials for the workshop app.', 'starts_in_days' => 7, 'duration_hours' => 4, 'capacity' => 20],
            ['title' => 'Vue component design', 'description' => 'Patterns for Inertia pages and reusable UI pieces.', 'starts_in_days' => 8, 'duration_hours' => 3, 'capacity' => 12],
            ['title' => 'Database modeling', 'description' => 'Relationships, constraints, and realistic seed data.', 'starts_in_days' => 9, 'duration_hours' => 5, 'capacity' => 15],
            ['title' => 'Eloquent query tuning', 'description' => 'Avoiding N+1, eager loads, and chunking large result sets.', 'starts_in_days' => 10, 'duration_hours' => 3, 'capacity' => 18],
            ['title' => 'Pest and feature tests', 'description' => 'HTTP tests, Inertia assertions, and database refresh strategies.', 'starts_in_days' => 11, 'duration_hours' => 4, 'capacity' => 22],
            ['title' => 'Fortify and session auth', 'description' => 'Login flows, verification, and guard configuration.', 'starts_in_days' => 12, 'duration_hours' => 3, 'capacity' => 16],
            ['title' => 'Spatie Permission in practice', 'description' => 'Roles, permissions, and seeding strategies for multi-role apps.', 'starts_in_days' => 13, 'duration_hours' => 3, 'capacity' => 14],
            ['title' => 'API resources and policies', 'description' => 'Authorisation patterns that extend to future JSON surfaces.', 'starts_in_days' => 14, 'duration_hours' => 4, 'capacity' => 20],
            ['title' => 'Queues and Horizon basics', 'description' => 'Database driver, retries, and failure handling.', 'starts_in_days' => 15, 'duration_hours' => 3, 'capacity' => 18],
            ['title' => 'Mail and notifications', 'description' => 'Mailables, markdown templates, and the log driver in development.', 'starts_in_days' => 16, 'duration_hours' => 2, 'capacity' => 25],
            ['title' => 'Vite and Tailwind workflow', 'description' => 'Building assets, HMR with Sail, and design tokens.', 'starts_in_days' => 17, 'duration_hours' => 3, 'capacity' => 24],
            ['title' => 'TypeScript with Vue 3', 'description' => 'Props, composables, and typed route helpers.', 'starts_in_days' => 18, 'duration_hours' => 4, 'capacity' => 20],
            ['title' => 'Wayfinder route generation', 'description' => 'Keeping frontend URLs aligned with Laravel routes.', 'starts_in_days' => 19, 'duration_hours' => 2, 'capacity' => 16],
            ['title' => 'Validation and form requests', 'description' => 'Centralising rules and error messages for Inertia forms.', 'starts_in_days' => 20, 'duration_hours' => 3, 'capacity' => 22],
            ['title' => 'Localisation and dates', 'description' => 'Carbon, time zones, and workshop schedules.', 'starts_in_days' => 21, 'duration_hours' => 2, 'capacity' => 18],
            ['title' => 'Factories and deterministic seeds', 'description' => 'Readable demo data without collisions on unique indexes.', 'starts_in_days' => 22, 'duration_hours' => 3, 'capacity' => 15],
            ['title' => 'Sail and container workflows', 'description' => 'MySQL, Redis, and one-off Artisan inside Docker.', 'starts_in_days' => 23, 'duration_hours' => 2, 'capacity' => 30],
            ['title' => 'CI pipelines for Laravel', 'description' => 'Composer, npm, Pint, and Pest in automated checks.', 'starts_in_days' => 24, 'duration_hours' => 3, 'capacity' => 20],
            ['title' => 'Blade vs Inertia trade-offs', 'description' => 'When to stay server-rendered versus SPA-style navigation.', 'starts_in_days' => 25, 'duration_hours' => 2, 'capacity' => 14],
            ['title' => 'Caching strategies', 'description' => 'Array store in tests, database cache in this project setup.', 'starts_in_days' => 26, 'duration_hours' => 3, 'capacity' => 17],
            ['title' => 'Scheduled tasks and reminders', 'description' => 'Foundation for future workshop reminder commands.', 'starts_in_days' => 27, 'duration_hours' => 2, 'capacity' => 19],
            ['title' => 'File storage and uploads', 'description' => 'Public disk, validation, and future workshop materials.', 'starts_in_days' => 28, 'duration_hours' => 3, 'capacity' => 16],
            ['title' => 'Multi-step enrolment flows', 'description' => 'State machines with confirmed versus waiting list.', 'starts_in_days' => 29, 'duration_hours' => 4, 'capacity' => 12],
            ['title' => 'Capacity and waitlists', 'description' => 'Modelling full sessions without breaking unique enrolment keys.', 'starts_in_days' => 30, 'duration_hours' => 3, 'capacity' => 1],
            ['title' => 'Overlap detection patterns', 'description' => 'Using starts_at and ends_at for scheduling rules.', 'starts_in_days' => 31, 'duration_hours' => 3, 'capacity' => 2],
            ['title' => 'Dashboard metrics sketch', 'description' => 'Aggregates for admins without eager-loading the world.', 'starts_in_days' => 32, 'duration_hours' => 2, 'capacity' => 21],
            ['title' => 'Employee self-service roadmap', 'description' => 'Browsing workshops and applying constraints in policies.', 'starts_in_days' => 33, 'duration_hours' => 2, 'capacity' => 23],
            ['title' => 'Admin workshop CRUD roadmap', 'description' => 'Forms, policies, and auditing changes.', 'starts_in_days' => 34, 'duration_hours' => 4, 'capacity' => 14],
            ['title' => 'Observability and logging', 'description' => 'Pail, structured context, and debugging queue jobs.', 'starts_in_days' => 35, 'duration_hours' => 2, 'capacity' => 26],
            ['title' => 'Security headers and HTTPS', 'description' => 'Production hardening beyond the local stack.', 'starts_in_days' => 36, 'duration_hours' => 2, 'capacity' => 19],
            ['title' => 'Rate limiting and throttles', 'description' => 'Protecting auth and sensitive mutations.', 'starts_in_days' => 37, 'duration_hours' => 2, 'capacity' => 17],
            ['title' => 'Database transactions', 'description' => 'All-or-nothing enrolment when business rules grow.', 'starts_in_days' => 38, 'duration_hours' => 3, 'capacity' => 15],
            ['title' => 'Soft deletes and auditing', 'description' => 'Keeping history when workshops are retired.', 'starts_in_days' => 39, 'duration_hours' => 2, 'capacity' => 13],
            ['title' => 'Search and filtering', 'description' => 'Indexing starts_at for upcoming lists.', 'starts_in_days' => 40, 'duration_hours' => 3, 'capacity' => 22],
            ['title' => 'Performance profiling', 'description' => 'Laravel Debugbar, slow queries, and fixes.', 'starts_in_days' => 41, 'duration_hours' => 2, 'capacity' => 24],
            ['title' => 'Deployment checklist', 'description' => 'Env, migrations, caches, and zero-downtime habits.', 'starts_in_days' => 42, 'duration_hours' => 3, 'capacity' => 28],
            ['title' => 'Team conventions recap', 'description' => 'Docs, tests, and seeds that stay maintainable.', 'starts_in_days' => 43, 'duration_hours' => 2, 'capacity' => 30],
        ];
    }

    /**
     * @param  Collection<int, User>  $employees
     */
    private function seedRegistrationsForWorkshop(Workshop $workshop, int $index, Collection $employees): void
    {
        $count = $employees->count();
        $e0 = $employees[$index % $count];
        $e1 = $employees[($index + 1) % $count];
        $e2 = $employees[($index + 2) % $count];

        $capacity = $workshop->capacity;

        if ($capacity >= 3) {
            WorkshopRegistration::create([
                'workshop_id' => $workshop->id,
                'user_id' => $e0->id,
                'status' => WorkshopRegistrationStatus::Confirmed,
            ]);
            WorkshopRegistration::create([
                'workshop_id' => $workshop->id,
                'user_id' => $e1->id,
                'status' => WorkshopRegistrationStatus::Confirmed,
            ]);
            WorkshopRegistration::create([
                'workshop_id' => $workshop->id,
                'user_id' => $e2->id,
                'status' => WorkshopRegistrationStatus::Confirmed,
            ]);

            return;
        }

        if ($capacity === 2) {
            WorkshopRegistration::create([
                'workshop_id' => $workshop->id,
                'user_id' => $e0->id,
                'status' => WorkshopRegistrationStatus::Confirmed,
            ]);
            WorkshopRegistration::create([
                'workshop_id' => $workshop->id,
                'user_id' => $e1->id,
                'status' => WorkshopRegistrationStatus::Confirmed,
            ]);
            WorkshopRegistration::create([
                'workshop_id' => $workshop->id,
                'user_id' => $e2->id,
                'status' => WorkshopRegistrationStatus::WaitingList,
            ]);

            return;
        }

        // capacity === 1
        WorkshopRegistration::create([
            'workshop_id' => $workshop->id,
            'user_id' => $e0->id,
            'status' => WorkshopRegistrationStatus::Confirmed,
        ]);
        WorkshopRegistration::create([
            'workshop_id' => $workshop->id,
            'user_id' => $e1->id,
            'status' => WorkshopRegistrationStatus::WaitingList,
        ]);
    }
}
