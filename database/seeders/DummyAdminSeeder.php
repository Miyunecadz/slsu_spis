<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::truncate();
        $users = Admin::factory()->count(1)->create();

        foreach($users as $user)
        {
            User::factory()->create([
                'account_type' => 1,
                'user_id' => $user->id,
            ]);
        }
    }
}
