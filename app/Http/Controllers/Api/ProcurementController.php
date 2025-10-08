<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProcurementResource;
use App\Models\Procurement;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Fetch the data from the database
        $procurements = Procurement::with(['division', 'endUser', 'category', 'categoryType', 'bacType', 'venueSpecific', 'venueProvincesHUC', 'fundSource'])->get();

        // 2. Wrap the collection in your resource
        return ProcurementResource::collection($procurements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Procurement $procurement)
    {
        // Use load() to eager-load relationships on an already-fetched model
        $procurement->load(['division', 'endUser', 'category', 'categoryType', 'bacType', 'venueSpecific', 'venueProvincesHUC', 'fundSource']);

        return new ProcurementResource($procurement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
