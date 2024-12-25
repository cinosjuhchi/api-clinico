<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use Illuminate\Console\Command;

class UpdateLeaveBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave-balances:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update leave balances (bal, bal_bf, burned, taken) every 31st December';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting leave balance update process...');

        $leaveBalances = LeaveBalance::all();

        foreach ($leaveBalances as $leaveBalance) {
            $remainingBal = $leaveBalance->bal;
            $balBF = floor($remainingBal / 2);
            $burned = $remainingBal - $balBF;

            // update coy..
            $leaveBalance->bal_bf = $balBF;
            $leaveBalance->burned = $burned;
            $leaveBalance->bal = $leaveBalance->bal_bf;
            $leaveBalance->save();
        }

        $this->info('Leave balance update process completed.');

        return 0;
    }
}
