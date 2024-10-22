<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PregnancyCategory;
use Illuminate\Routing\Controller;
use App\Http\Resources\PregnancyCategoryResource;
use App\Http\Requests\StorePregnancyCategoryRequest;
use App\Http\Requests\UpdatePregnancyCategoryRequest;

class PregnancyCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pregnancyCategories = PregnancyCategory::all();
        return response()->json(PregnancyCategoryResource::collection($pregnancyCategories));
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
    public function store(StorePregnancyCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PregnancyCategory $pregnancyCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PregnancyCategory $pregnancyCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePregnancyCategoryRequest $request, PregnancyCategory $pregnancyCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PregnancyCategory $pregnancyCategory)
    {
        //
    }
}
