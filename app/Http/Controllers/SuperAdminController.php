<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use App\Http\Requests\StoreSuperAdminRequest;
use App\Http\Requests\UpdateSuperAdminRequest;

class SuperAdminController extends Controller
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
    public function store(StoreSuperAdminRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SuperAdmin $superAdmin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuperAdmin $superAdmin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSuperAdminRequest $request, SuperAdmin $superAdmin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuperAdmin $superAdmin)
    {
        //
    }
}
