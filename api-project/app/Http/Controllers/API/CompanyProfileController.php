<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyProfile;

class CompanyProfileController extends Controller
{
    public function show()
    {
        return CompanyProfile::first();
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $company = CompanyProfile::first();
        if ($company) {
            $company->update($validated);
        } else {
            $company = CompanyProfile::create($validated);
        }

        return response()->json(['message' => 'Company profile updated successfully']);
    }
}
