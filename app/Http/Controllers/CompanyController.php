<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $sortField = $request->input('sortField', 'name');
        $sortDirection = $request->input('sortDirection', 'asc');
        $industry = $request->input('industry');
        
        $query = Company::with(['owner'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('website', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('industry', 'like', "%{$search}%");
                });
            })
            ->when($industry, function ($query, $industry) {
                $query->where('industry', $industry);
            });
        
        // Only show companies owned by user unless admin/manager
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            $query->where('owner_id', Auth::id());
        }
        
        $companies = $query->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->appends($request->all());
            
        $industries = Company::distinct('industry')->whereNotNull('industry')->pluck('industry');
        
        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'industries' => $industries,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
                'industry' => $industry,
            ],
            'can' => [
                'create' => Auth::user()->can('create-companies') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'edit' => Auth::user()->can('edit-companies') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-companies') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        return Inertia::render('Companies/Create', [
            'users' => $users,
            'industries' => [
                'Technology', 'Manufacturing', 'Healthcare', 'Financial Services', 
                'Retail', 'Education', 'Construction', 'Entertainment', 
                'Hospitality', 'Transportation', 'Other'
            ],
        ]);
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'employees_count' => 'nullable|integer',
            'annual_revenue' => 'nullable|numeric',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        
        $validated['created_by'] = Auth::id();
        
        if (!isset($validated['owner_id'])) {
            $validated['owner_id'] = Auth::id();
        }
        
        $company = Company::create($validated);
        
        return redirect()->route('companies.show', $company)
            ->with('success', 'Company created successfully.');
    }
}