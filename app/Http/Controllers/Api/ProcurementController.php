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
        $procurements = Procurement::paginate(15);

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
        // The procurement is already fetched by route model binding.
        // Just wrap the single model in your resource.
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
