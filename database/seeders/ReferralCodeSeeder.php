<?php

namespace Database\Seeders;

use App\Helpers\ReferralCodeHelper;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferralCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUsers = User::where('role', 'admin')->get();

        foreach ($adminUsers as $user) {
            if (!$user->referralCode) {
                ReferralCode::create([
                    'user_id' => $user->id,
                    'code' => ReferralCodeHelper::generateCode(),
                ]);
            }
        }
        $this->command->info('Successfully created referral codes for admin users.');
    }
}
