<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMonthlyPayslipRequest;
use App\Repositories\MonthlyPayslipRepository;
use App\Service\MonthlyPayslipService;
use Illuminate\Http\Request;

class MonthlyPayslipController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(MonthlyPayslipRepository $repository, MonthlyPayslipService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return response()->json($this->repository->getAll($request->search, $request->month));
    }

    public function store(StoreMonthlyPayslipRequest $request)
    {
        $data = array_merge($request->all(), $this->service->calculateSalaries($request->all()));
        return response()->json($this->repository->create($data));
    }

    public function show($id)
    {
        return response()->json($this->repository->findById($id));
    }

    public function update(Request $request, $id)
    {
        $data = array_merge($request->all(), $this->service->calculateSalaries($request->all()));
        return response()->json($this->repository->update($id, $data));
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Payslip deleted successfully', 'status' => $this->repository->delete($id)]);
    }
}
