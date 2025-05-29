<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Customer::select('id', 'name', 'email', 'phone', 'address')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255|unique:customers,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create($request->only(['name', 'email', 'phone', 'address']));

        return response()->json([
            'message' => 'Customer created successfully.',
            'data'    => $customer->only(['id', 'name', 'email', 'phone', 'address']),
        ], 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($request->only(['name', 'email', 'phone', 'address']));

        return response()->json([
            'message' => 'Customer updated successfully.',
            'data'    => $customer->only(['id', 'name', 'email', 'phone', 'address']),
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully.'
        ]);
    }
}
