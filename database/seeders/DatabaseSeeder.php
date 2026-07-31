<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@smartlearn.test'],
            [
                'name' => 'SmartLearn Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        $instructor = User::query()->updateOrCreate(
            ['email' => 'instructor@smartlearn.test'],
            [
                'name' => 'Sara Instructor',
                'password' => Hash::make('password'),
                'role' => UserRole::Instructor,
                'email_verified_at' => now(),
            ]
        );

        $student = User::query()->updateOrCreate(
            ['email' => 'student@smartlearn.test'],
            [
                'name' => 'Omar Student',
                'password' => Hash::make('password'),
                'role' => UserRole::Student,
                'email_verified_at' => now(),
            ]
        );

        $course = Course::query()->updateOrCreate(
            ['title' => 'Laravel Authentication Masterclass'],
            [
                'description' => 'Learn Session, Sanctum, Passport, and JWT with real LMS scenarios.',
                'instructor_id' => $instructor->id,
            ]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Session Cookies 101'],
            ['video_url' => 'https://example.com/videos/session', 'sort_order' => 1]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Sanctum Personal Access Tokens'],
            ['video_url' => 'https://example.com/videos/sanctum', 'sort_order' => 2]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Passport OAuth2 Scopes'],
            ['video_url' => 'https://example.com/videos/passport', 'sort_order' => 3]
        );

        Enrollment::query()->updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            ['enrolled_at' => now()]
        );

        $exam = Exam::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Auth Methods Quiz']
        );

        Grade::query()->updateOrCreate(
            ['student_id' => $student->id, 'exam_id' => $exam->id],
            ['course_id' => $course->id, 'score' => 92.5]
        );

        Certificate::query()->updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            ['code' => 'CERT-DEMO0001', 'issued_at' => now()]
        );

        Meeting::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Live Office Hours'],
            [
                'meeting_url' => 'https://zoom.us/j/smartlearn-demo',
                'starts_at' => now()->addDays(2),
            ]
        );

        $this->seedOAuthClients();

        $this->command?->info('Demo users (password: password):');
        $this->command?->info('  admin@smartlearn.test');
        $this->command?->info('  instructor@smartlearn.test');
        $this->command?->info('  student@smartlearn.test');
    }

    private function seedOAuthClients(): void
    {
        $clients = app(ClientRepository::class);
        $exported = [];

        $definitions = [
            [
                'name' => 'ABC University',
                'scopes' => 'courses.read students.read',
            ],
            [
                'name' => 'Zoom',
                'scopes' => 'meetings.read courses.read',
            ],
            [
                'name' => 'ExamPro',
                'scopes' => 'grades.read',
            ],
            [
                'name' => 'Certificate Generator',
                'scopes' => 'certificates.write',
            ],
        ];

        foreach ($definitions as $definition) {
            $existing = Client::query()
                ->where('name', $definition['name'])
                ->first();

            if ($existing) {
                $exported[] = [
                    'name' => $existing->name,
                    'client_id' => $existing->id,
                    'client_secret' => '(already existed — recreate to get a new secret)',
                    'grant' => 'client_credentials',
                    'scopes' => $definition['scopes'],
                ];

                continue;
            }

            $client = $clients->createClientCredentialsGrantClient($definition['name']);

            $exported[] = [
                'name' => $client->name,
                'client_id' => $client->id,
                'client_secret' => $client->plainSecret,
                'grant' => 'client_credentials',
                'scopes' => $definition['scopes'],
            ];

            $this->command?->warn("OAuth client created: {$definition['name']}");
            $this->command?->line("  client_id: {$client->id}");
            $this->command?->line("  client_secret: {$client->plainSecret}");
            $this->command?->line("  intended_scopes: {$definition['scopes']}");
        }

        $authClient = Client::query()
            ->where('name', 'ABC University Portal (Auth Code)')
            ->first();

        if (! $authClient) {
            $authClient = $clients->createAuthorizationCodeGrantClient(
                'ABC University Portal (Auth Code)',
                [config('app.url').'/learn/passport'],
                true
            );

            $this->command?->warn('OAuth Auth Code client created: ABC University Portal (Auth Code)');
            $this->command?->line("  client_id: {$authClient->id}");
            $this->command?->line("  client_secret: {$authClient->plainSecret}");
            $this->command?->line('  redirect: '.config('app.url').'/learn/passport');
        }

        $exported[] = [
            'name' => $authClient->name,
            'client_id' => $authClient->id,
            'client_secret' => $authClient->plainSecret ?? '(already existed)',
            'grant' => 'authorization_code',
            'scopes' => 'courses.read students.read',
            'redirect' => config('app.url').'/learn/passport',
        ];

        file_put_contents(
            storage_path('oauth-demo-clients.json'),
            json_encode($exported, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
