<?php

namespace App\Repositories;

use App\Models\MonthlyPayslip;

class MonthlyPayslipRepository
{
    /**
     * Get all monthly payslips.
     */
    public function getAll($search = null, $month = null)
    {
        $data = MonthlyPayslip::when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nric', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
                });
            })->when($month, function ($query) use ($month) {
                $query->whereMonth('date', $month);
            });

        $user = auth()->user();
        if ($user->role === "clinic") {
            $data->where('clinic_id', $user->clinic->id);
        } else if ($user->role === "superadmin") {
            $data->whereNull('clinic_id');
        } else {
            $data->where('user_id', $user->id);
        }

        return $data
                ->orderBy('date', 'desc')
                ->paginate(10);
    }

    /**
     * Get detail of single data monthly
     */
    public function findById($id)
    {
        $payslip = MonthlyPayslip::findOrFail($id);

        $year = date('Y', strtotime($payslip->date));
        $month = date('m', strtotime($payslip->date));

        $ytdPayslips = MonthlyPayslip::where('user_id', $payslip->user_id)
            ->whereYear('date', $year)
            ->whereMonth('date', '<=', $month)
            ->get();

        $ytd = [
            'empee_kwsp' => 0,
            'empee_perkeso' => 0,
            'empee_eis' => 0,
            'empee_tax' => 0,
            'empeer_kwsp' => 0,
            'empeer_perkeso' => 0,
            'empeer_eis' => 0,
            'empeer_tax' => 0,
            'total_kwsp_ytd' => 0,
            'total_perkeso_ytd' => 0,
            'total_eis_ytd' => 0,
            'total_tax_ytd' => 0,
        ];

        foreach ($ytdPayslips as $ytdPayslip) {
            $ytd['empee_kwsp'] += floatval($ytdPayslip->kwsp);
            $ytd['empee_perkeso'] += floatval($ytdPayslip->perkeso);
            $ytd['empee_eis'] += floatval($ytdPayslip->eis);
            $ytd['empee_tax'] += floatval($ytdPayslip->tax);

            $ytd['empeer_kwsp'] += floatval($ytdPayslip->kwsp_employer);
            $ytd['empeer_perkeso'] += floatval($ytdPayslip->perkeso_employer);
            $ytd['empeer_eis'] += floatval($ytdPayslip->eis_employer);
            $ytd['empeer_tax'] += 0;

            $ytd['total_kwsp_ytd'] += floatval($ytdPayslip->total_kwsp);
            $ytd['total_perkeso_ytd'] += floatval($ytdPayslip->total_perkeso);
            $ytd['total_eis_ytd'] += floatval($ytdPayslip->total_eis);
            $ytd['total_tax_ytd'] += floatval($ytdPayslip->tax);
        }

        $ytdFormatted = [
            'employee' => [
                'kwsp' => number_format($ytd['empee_kwsp'], 2),
                'perkeso' => number_format($ytd['empee_perkeso'], 2),
                'eis' => number_format($ytd['empee_eis'], 2),
                'tax' => number_format($ytd['empee_tax'], 2),
            ],
            'employer' => [
                'kwsp' => number_format($ytd['empeer_kwsp'], 2),
                'perkeso' => number_format($ytd['empeer_perkeso'], 2),
                'eis' => number_format($ytd['empeer_eis'], 2),
                'tax' => number_format($ytd['empeer_tax'], 2),
            ],
            'total YTD' => [
                'kwsp' => number_format($ytd['total_kwsp_ytd'], 2),
                'perkeso' => number_format($ytd['total_perkeso_ytd'], 2),
                'eis' => number_format($ytd['total_eis_ytd'], 2),
                'tax' => number_format($ytd['total_tax_ytd'], 2),
            ],
        ];

        $payslip->year_to_date = $ytdFormatted;

        return $payslip;
    }

    /**
     * Store new payslip
     */
    public function create($data)
    {
        return MonthlyPayslip::create($data);
    }

    /**
     * Update existing payslip
     */
    public function update($id, $data)
    {
        $payslip = MonthlyPayslip::findOrFail($id);
        $payslip->update($data);
        return $payslip;
    }

    /**
     * Delete existing payslip
     */
    public function delete($id)
    {
        return MonthlyPayslip::destroy($id);
    }
}
