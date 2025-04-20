<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\User;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Product;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DealController extends Controller
{
    /**
     * Display a listing of the deals.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $sortField = $request->input('sortField', 'created_at');
        $sortDirection = $request->input('sortDirection', 'desc');
        $stage = $request->input('stage', '');
        $companyId = $request->input('company_id');
        
        $query = Deal::with(['company', 'primaryContact', 'owner'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('amount', 'like', "%{$search}%")
                      ->orWhereHas('company', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('primaryContact', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($stage, function ($query, $stage) {
                $query->where('pipeline_stage', $stage);
            })
            ->when($companyId, function ($query, $companyId) {
                $query->where('company_id', $companyId);
            });
            
        // Only show deals owned by user unless admin/manager
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            $query->where('owner_id', Auth::id());
        }
        
        $deals = $query->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->appends($request->all());
            
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        
        // Get pipeline stages from database if available, otherwise use hardcoded list
        try {
            $stages = PipelineStage::orderBy('display_order')->pluck('name');
        } catch (\Exception $e) {
            $stages = collect(['Qualification', 'Needs Analysis', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost']);
        }
        
        return Inertia::render('Deals/Index', [
            'deals' => $deals,
            'companies' => $companies,
            'stages' => $stages->unique()->values(),
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
                'stage' => $stage,
                'companyId' => $companyId,
            ],
            'stats' => [
                'total' => Deal::count(),
                'open' => Deal::whereNull('won')->count(),
                'won' => Deal::where('won', true)->count(),
                'lost' => Deal::where('won', false)->count(),
                'value' => Deal::whereNull('won')->sum('amount'),
            ],
            'can' => [
                'create' => Auth::user()->can('create-deals') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'edit' => Auth::user()->can('edit-deals') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-deals') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new deal.
     */
    public function create(Request $request)
    {
        $contactId = $request->input('contact_id');
        $companyId = $request->input('company_id');
        
        $contact = null;
        if ($contactId) {
            $contact = Contact::with('company')->find($contactId);
            // If contact has a company, pre-select it
            if ($contact && $contact->company_id) {
                $companyId = $contact->company_id;
            }
        }
        
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $contacts = [];
        
        if ($companyId) {
            $contacts = Contact::where('company_id', $companyId)->get(['id', 'first_name', 'last_name']);
        }
        
        $products = Product::select('id', 'name', 'unit_price')->get();
        
        // Get pipeline stages from database if available, otherwise use hardcoded list
        try {
            $pipelineStages = PipelineStage::orderBy('display_order')->pluck('name');
        } catch (\Exception $e) {
            $pipelineStages = ['Qualification', 'Needs Analysis', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'];
        }
        
        return Inertia::render('Deals/Create', [
            'users' => $users,
            'companies' => $companies,
            'contacts' => $contacts,
            'products' => $products,
            'preselectedContact' => $contact,
            'preselectedCompany' => $companyId ? Company::find($companyId) : null,
            'pipelineStages' => $pipelineStages,
            'sources' => ['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'],
        ]);
    }

    /**
     * Store a newly created deal in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'primary_contact_id' => 'nullable|exists:contacts,id',
            'amount' => 'nullable|numeric|min:0',
            'pipeline_stage' => 'required|string|max:255',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'source' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'contacts' => 'nullable|array',
            'contacts.*.id' => 'required|exists:contacts,id',
            'contacts.*.role' => 'required|string',
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
        ]);
        
        // Set created_by
        $validated['created_by'] = Auth::id();
        
        // Set won/lost based on pipeline stage
        if ($validated['pipeline_stage'] === 'Closed Won') {
            $validated['won'] = true;
            $validated['actual_close_date'] = now();
        } else if ($validated['pipeline_stage'] === 'Closed Lost') {
            $validated['won'] = false;
            $validated['actual_close_date'] = now();
        }
        
        // Create deal
        $deal = Deal::create($validated);
        
        // Attach contacts
        if (!empty($validated['contacts'])) {
            foreach ($validated['contacts'] as $contactData) {
                $deal->contacts()->attach($contactData['id'], [
                    'role' => $contactData['role']
                ]);
            }
        }
        
        // Attach products
        if (!empty($validated['products'])) {
            foreach ($validated['products'] as $productData) {
                $total_price = $productData['quantity'] * $productData['unit_price'];
                if (isset($productData['discount']) && $productData['discount'] > 0) {
                    $discount_amount = $productData['discount'];
                    $total_price -= $discount_amount;
                    
                    // Calculate discount percent
                    $original_price = $productData['quantity'] * $productData['unit_price'];
                    $discount_percent = ($discount_amount / $original_price) * 100;
                    
                    $deal->products()->attach($productData['id'], [
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'discount_amount' => $discount_amount,
                        'discount_percent' => round($discount_percent, 2),
                        'total_price' => $total_price
                    ]);
                } else {
                    $deal->products()->attach($productData['id'], [
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'discount_amount' => 0,
                        'discount_percent' => 0,
                        'total_price' => $total_price
                    ]);
                }
            }
        }
        
        return redirect()->route('deals.show', $deal)
            ->with('success', 'Deal created successfully.');
    }

    /**
     * Display the specified deal.
     */
    public function show(Deal $deal)
    {
        $deal->load([
            'company',
            'primaryContact',
            'owner',
            'creator',
            'contacts',
            'products' => function ($query) {
                $query->withPivot(['quantity', 'unit_price', 'discount_amount', 'discount_percent', 'total_price']);
            },
            'notes' => function ($query) {
                $query->with('creator')->latest();
            },
            'activities' => function ($query) {
                $query->with('owner', 'creator')->latest();
            },
            'tasks' => function ($query) {
                $query->with('assignee')->latest();
            },
            'convertedFromLead',
        ]);
        
        // Calculate total value from product prices
        $totalValue = $deal->products->sum(function ($product) {
            return $product->pivot->total_price;
        });
        
        // If no products, use the deal amount
        if ($totalValue == 0 && $deal->amount) {
            $totalValue = $deal->amount;
        }
        
        return Inertia::render('Deals/Show', [
            'deal' => $deal,
            'totalValue' => $totalValue,
            'can' => [
                'edit' => Auth::user()->can('edit-deals') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-deals') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified deal.
     */
    public function edit(Deal $deal)
    {
        $deal->load(['company', 'primaryContact', 'contacts', 'products']);
        
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $contacts = [];
        
        if ($deal->company_id) {
            $contacts = Contact::where('company_id', $deal->company_id)->get(['id', 'first_name', 'last_name']);
        }
        
        $products = Product::select('id', 'name', 'unit_price')->get();
        
        // Get pipeline stages from database if available, otherwise use hardcoded list
        try {
            $pipelineStages = PipelineStage::orderBy('display_order')->pluck('name');
        } catch (\Exception $e) {
            $pipelineStages = ['Qualification', 'Needs Analysis', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'];
        }
        
        return Inertia::render('Deals/Edit', [
            'deal' => $deal,
            'dealContacts' => $deal->contacts->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->full_name,
                    'role' => $contact->pivot->role
                ];
            }),
            'dealProducts' => $deal->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'unit_price' => $product->pivot->unit_price,
                    'discount' => $product->pivot->discount_amount ?? 0,
                    'total' => $product->pivot->total_price
                ];
            }),
            'users' => $users,
            'companies' => $companies,
            'contacts' => $contacts,
            'products' => $products,
            'pipelineStages' => $pipelineStages,
            'sources' => ['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'],
        ]);
    }

    /**
     * Update the specified deal in storage.
     */
    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'primary_contact_id' => 'nullable|exists:contacts,id',
            'amount' => 'nullable|numeric|min:0',
            'pipeline_stage' => 'required|string|max:255',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'source' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'contacts' => 'nullable|array',
            'contacts.*.id' => 'required|exists:contacts,id',
            'contacts.*.role' => 'required|string',
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
        ]);
        
        // Set won/lost based on pipeline stage
        if ($validated['pipeline_stage'] === 'Closed Won' && $deal->won !== true) {
            $validated['won'] = true;
            $validated['actual_close_date'] = now();
        } else if ($validated['pipeline_stage'] === 'Closed Lost' && $deal->won !== false) {
            $validated['won'] = false;
            $validated['actual_close_date'] = now();
        } else if ($validated['pipeline_stage'] !== 'Closed Won' && $validated['pipeline_stage'] !== 'Closed Lost') {
            $validated['won'] = null;
            $validated['actual_close_date'] = null;
            $validated['lost_reason'] = null;
        }
        
        // Update deal
        $deal->update($validated);
        
        // Sync contacts
        $contactsSync = [];
        if (!empty($validated['contacts'])) {
            foreach ($validated['contacts'] as $contactData) {
                $contactsSync[$contactData['id']] = ['role' => $contactData['role']];
            }
        }
        $deal->contacts()->sync($contactsSync);
        
        // Sync products
        $productsSync = [];
        if (!empty($validated['products'])) {
            foreach ($validated['products'] as $productData) {
                $total_price = $productData['quantity'] * $productData['unit_price'];
                if (isset($productData['discount']) && $productData['discount'] > 0) {
                    $discount_amount = $productData['discount'];
                    $total_price -= $discount_amount;
                    
                    // Calculate discount percent
                    $original_price = $productData['quantity'] * $productData['unit_price'];
                    $discount_percent = ($discount_amount / $original_price) * 100;
                    
                    $productsSync[$productData['id']] = [
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'discount_amount' => $discount_amount,
                        'discount_percent' => round($discount_percent, 2),
                        'total_price' => $total_price
                    ];
                } else {
                    $productsSync[$productData['id']] = [
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'discount_amount' => 0,
                        'discount_percent' => 0,
                        'total_price' => $total_price
                    ];
                }
            }
        }
        $deal->products()->sync($productsSync);
        
        return redirect()->route('deals.show', $deal)
            ->with('success', 'Deal updated successfully.');
    }

    /**
     * Remove the specified deal from storage.
     */
    public function destroy(Deal $deal)
    {
        $deal->delete();
        
        return redirect()->route('deals.index')
            ->with('success', 'Deal deleted successfully.');
    }

    /**
     * Change the deal status.
     */
    public function changeStatus(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'pipeline_stage' => 'required|string|max:255',
            'lost_reason' => 'nullable|required_if:pipeline_stage,Closed Lost|string',
        ]);
        
        $updateData = ['pipeline_stage' => $validated['pipeline_stage']];
        
        // Set won/lost status based on pipeline stage
        if ($validated['pipeline_stage'] === 'Closed Won') {
            $updateData['won'] = true;
            $updateData['actual_close_date'] = now();
        } else if ($validated['pipeline_stage'] === 'Closed Lost') {
            $updateData['won'] = false;
            $updateData['actual_close_date'] = now();
            
            if (isset($validated['lost_reason'])) {
                $updateData['lost_reason'] = $validated['lost_reason'];
            }
        } else {
            // For other stages, reset the won/lost status
            $updateData['won'] = null;
            $updateData['actual_close_date'] = null;
            $updateData['lost_reason'] = null;
        }
        
        $deal->update($updateData);
        
        return redirect()->back()->with('success', 'Deal updated successfully.');
    }

    /**
     * Add a note to the deal.
     */
    public function addNote(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        
        $validated['created_by'] = Auth::id();
        
        $note = $deal->notes()->create($validated);
        
        return redirect()->back()->with('success', 'Note added successfully.');
    }

    /**
     * Display the pipeline view of deals.
     */
    public function pipeline(Request $request)
    {
        $search = $request->input('search', '');
        $ownerId = $request->input('owner_id');
        $companyId = $request->input('company_id');
        
        // Get pipeline stages for proper ordering
        try {
            $pipelineStages = PipelineStage::orderBy('display_order')->get();
            $stageNames = $pipelineStages->pluck('name')->toArray();
        } catch (\Exception $e) {
            $stageNames = ['Qualification', 'Needs Analysis', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'];
        }
        
        $query = Deal::with(['owner', 'company', 'primaryContact'])
            // Only show open and won deals in pipeline
            ->where(function ($query) {
                $query->whereNull('won')  // In progress deals
                      ->orWhere('won', true);  // Won deals
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('company', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($ownerId, function ($query, $ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            });
        
        // Only show deals owned by user unless admin/manager
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            $query->where('owner_id', Auth::id());
        }
        
        $deals = $query->get();
        
        // Calculate statistics for each stage
        $dealStats = [
            'total_count' => $deals->count(),
            'total_value' => $deals->sum('amount'),
        ];
        
        // Add stats for each stage
        foreach ($stageNames as $stage) {
            $stageDeals = $deals->where('pipeline_stage', $stage);
            $stageName = strtolower(str_replace(' ', '_', $stage));
            
            $dealStats["{$stageName}_count"] = $stageDeals->count();
            $dealStats["{$stageName}_value"] = $stageDeals->sum('amount');
        }
        
        // Get users for owner filter
        $owners = User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name']);
        
        // Get companies for filter
        $companies = Company::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Deals/Pipeline', [
            'deals' => $deals,
            'owners' => $owners,
            'companies' => $companies,
            'pipelineStages' => $stageNames,
            'dealStats' => $dealStats,
            'filters' => [
                'search' => $search,
                'owner_id' => $ownerId,
                'company_id' => $companyId,
            ],
            'can' => [
                'create' => Auth::user()->can('create-deals') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'edit' => Auth::user()->can('edit-deals') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
            ]
        ]);
    }
}