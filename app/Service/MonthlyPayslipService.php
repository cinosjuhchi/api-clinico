<?php

namespace App\Service;

use App\Models\User;
use App\Repositories\MonthlyPayslipRepository;

class MonthlyPayslipService
{
    protected $repository;

    public function __construct(MonthlyPayslipRepository $repository)
    {
        $this->repository = $repository;
    }

    public function calculateSalaries(array $data): array
    {
        // Ambil nilai dengan default 0 untuk mencegah error jika tidak ada key
        $basicSalary = $data['basic_salary'] ?? 0;
        $overtime = $data['overtime'] ?? 0;
        $saleIncentives = $data['sale_incentives'] ?? 0;
        $claim = $data['claim'] ?? 0;

        $kwsp = $data['kwsp'] ?? 0;
        $perkeso = $data['perkeso'] ?? 0;
        $tax = $data['tax'] ?? 0;
        $eis = $data['eis'] ?? 0;

        // Hitung total earnings dan deductions
        $totalEarnings = $overtime + $saleIncentives + $claim;
        $totalDeduction = $kwsp + $perkeso + $tax + $eis;
        $nettSalary = $basicSalary + $totalEarnings - $totalDeduction;

        // Hitung kontribusi dari perusahaan
        $kwspEmployer = $basicSalary * 0.13;
        $perkesoEmployer = $basicSalary * 0.01733;
        $eisEmployer = $basicSalary * 0.00198;

        // Simpan hasil perhitungan ke dalam array
        return [
            'basic_salary' => $basicSalary,
            'total_earnings' => round($totalEarnings, 2),
            'total_deduction' => round($totalDeduction, 2),
            'nett_salary' => round($nettSalary, 2),

            // Kontribusi perusahaan
            'kwsp_employer' => round($kwspEmployer, 2),
            'perkeso_employer' => round($perkesoEmployer, 2),
            'eis_employer' => round($eisEmployer, 2),

            // Data untuk current month
            'total_kwsp' => round($kwsp + $kwspEmployer, 2),
            'total_perkeso' => round($perkeso + $perkesoEmployer, 2),
            'total_eis' => round($eis + $eisEmployer, 2),
        ];
    }

}
