<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportBugTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("report_bug_types")->insert([
            ["name" => "General Support"],
            ["name" => "Bug Report"],
            ["name" => "Feedback & Feature Request"],
            ["name" => "Account Suspension"],
            ["name" => "Something Else"],
        ]);
    }
}
