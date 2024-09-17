<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Create a Super Admin user
        $superAdmin = User::create([
            'name' => 'super admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('admin1234'), // You can set a stronger password
        ]);

        // Optionally, you can create a project and assign the Super Admin to it
        $project = Project::create([
            'name' => 'Default Project',
            'description' => 'This is a default project created by the Super Admin.',
        ]);

        // Attach the Super Admin to the project with the Super Admin role
        $superAdmin->projects()->attach($project->id, [
            'role' => 'super admin',
            'contribution_hours' => 0,
            'last_activity' => now(),
        ]);

        // Optionally, display a message to confirm the user has been seeded
        $this->command->info('Super Admin user seeded successfully!');
    }
}