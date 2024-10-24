<?php

namespace App\Http\Controllers;

use App\Models\FinancialInformation;
use App\Http\Requests\StoreFinancialInformationRequest;
use App\Http\Requests\UpdateFinancialInformationRequest;

class FinancialInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFinancialInformationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialInformation $financialInformation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinancialInformation $financialInformation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFinancialInformationRequest $request, FinancialInformation $financialInformation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialInformation $financialInformation)
    {
        //
    }
}
