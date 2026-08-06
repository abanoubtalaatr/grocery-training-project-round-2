<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a default admin user if one does not already exist.
     */
    public function run(): void
    {
        $admins = [
            [
                'username'           => 'admin',
                'firstname'          => 'Super',
                'lastname'           => 'Admin',
                'email'              => 'admin@example.com',
                'password'           => Hash::make('admin123'),
                'is_admin'           => true,
                'is_active'          => true,
                'email_verified'     => true,
                'email_verified_at'  => now(),
                'agree_terms'        => true,
                'country_code'       => '+966',
            ],
        ];

        foreach ($admins as $data) {
            $user = User::withTrashed()->where('email', $data['email'])->first();

            if ($user) {
                // Restore if soft-deleted and ensure admin flag is set
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update([
                    'is_admin'  => true,
                    'is_active' => true,
                ]);

                $this->command->info("✔ Admin user already exists — updated: {$data['email']}");
            } else {
                User::create($data);
                $this->command->info("✔ Admin user created: {$data['email']}");
            }
        }

        $this->command->newLine();
        $this->command->line('  <fg=green>╔══════════════════════════════════════╗</>');
        $this->command->line('  <fg=green>║   Admin Dashboard Credentials        ║</>');
        $this->command->line('  <fg=green>╠══════════════════════════════════════╣</>');
        $this->command->line('  <fg=green>║</>  URL:      <fg=cyan>/dashboard</>                  <fg=green>║</>');
        $this->command->line('  <fg=green>║</>  Email:    <fg=yellow>admin@example.com</>          <fg=green>║</>');
        $this->command->line('  <fg=green>║</>  Password: <fg=yellow>admin123</>                   <fg=green>║</>');
        $this->command->line('  <fg=green>╚══════════════════════════════════════╝</>');
        $this->command->newLine();
        $this->command->warn('  ⚠  Change the password after first login!');
        $this->command->newLine();
    }
}
