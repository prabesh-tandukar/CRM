<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $sortField = $request->input('sortField', 'created_at');
        $sortDirection = $request->input('sortDirection', 'desc');
        $status = $request->input('status');
        $source = $request->input('source');
        
        $query = Lead::with(['owner'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('lead_status', $status);
            })
            ->when($source, function ($query, $source) {
                $query->where('lead_source', $source);
            });
        
        // Only show leads owned by user unless admin/manager
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            $query->where('owner_id', Auth::id());
        }
        
        // Default filter - exclude converted leads unless specifically viewing All or Converted
        if (!$status || ($status !== 'All' && $status !== 'Converted')) {
            $query->whereNull('converted_at');
        }
        
        $leads = $query->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->appends($request->all());
            
        $statuses = Lead::distinct('lead_status')->whereNotNull('lead_status')->pluck('lead_status');
        $sources = Lead::distinct('lead_source')->whereNotNull('lead_source')->pluck('lead_source');
        
        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'statuses' => collect(['All', 'New', 'Contacted', 'Qualified', 'Unqualified', 'Converted'])
                            ->merge($statuses)->unique()->values(),
            'sources' => collect(['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'])
                            ->merge($sources)->unique()->values(),
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
                'status' => $status,
                'source' => $source,
            ],
            'counts' => [
                'all' => Lead::count(),
                'new' => Lead::where('lead_status', 'New')->count(),
                'converted' => Lead::whereNotNull('converted_at')->count(),
            ],
            'can' => [
                'create' => Auth::user()->can('create-leads') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'edit' => Auth::user()->can('edit-leads') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-leads') || Auth::user()->hasRole(['Admin', 'Manager']),
                'convert' => Auth::user()->can('convert-leads') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        return Inertia::render('Leads/Create', [
            'users' => $users,
            'leadSources' => ['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'],
            'leadStatuses' => ['New', 'Contacted', 'Qualified', 'Unqualified'],
            'industries' => [
                'Technology', 'Manufacturing', 'Healthcare', 'Financial Services', 
                'Retail', 'Education', 'Construction', 'Entertainment', 
                'Hospitality', 'Transportation', 'Other'
            ],
        ]);
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'lead_status' => 'required|string|max:255',
            'estimated_budget' => 'nullable|numeric',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        
        $validated['created_by'] = Auth::id();
        
        if (!isset($validated['owner_id'])) {
            $validated['owner_id'] = Auth::id();
        }
        
        if (!isset($validated['lead_status'])) {
            $validated['lead_status'] = 'New';
        }
        
        $lead = Lead::create($validated);
        
        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'owner',
            'convertedContact',
            'convertedDeal',
            'notes' => function ($query) {
                $query->with('creator')->latest();
            },
            'activities' => function ($query) {
                $query->with('owner', 'creator')->latest();
            },
            'tasks' => function ($query) {
                $query->with('assignee')->latest();
            },
        ]);

        // Get notes separately to ensure they're loaded correctly
        $notes = $lead->notes()->with('creator')->latest()->get();

         // Check if user is authenticated before using Auth::user()->can()
        $user = Auth::user();
        
        return Inertia::render('Leads/Show', [
            'lead' => $lead,
        'isConverted' => !is_null($lead->converted_at),
        'can' => [
            'edit' => $user && ($user->can('edit-leads') || $user->hasRole(['Admin', 'Manager', 'Sales Rep'])),
            'delete' => $user && ($user->can('delete-leads') || $user->hasRole(['Admin', 'Manager'])),
            'convert' => $user && (($user->can('convert-leads') || $user->hasRole(['Admin', 'Manager', 'Sales Rep'])) && is_null($lead->converted_at)),
        ]
        ]);
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead)
    {
        // Don't allow editing converted leads
        if (!is_null($lead->converted_at)) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Cannot edit a converted lead.');
        }
        
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'users' => $users,
            'leadSources' => ['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'],
            'leadStatuses' => ['New', 'Contacted', 'Qualified', 'Unqualified'],
            'industries' => [
                'Technology', 'Manufacturing', 'Healthcare', 'Financial Services', 
                'Retail', 'Education', 'Construction', 'Entertainment', 
                'Hospitality', 'Transportation', 'Other'
            ],
        ]);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        // Don't allow updating converted leads
        if (!is_null($lead->converted_at)) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Cannot update a converted lead.');
        }
        
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'lead_status' => 'required|string|max:255',
            'estimated_budget' => 'nullable|numeric',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        
        $lead->update($validated);
        
        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        // Don't allow deleting converted leads
        if (!is_null($lead->converted_at)) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Cannot delete a converted lead.');
        }
        
        $lead->delete();
        
        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Show the form for converting a lead.
     */
    public function showConvert(Lead $lead)
    {
        // Don't allow converting already converted leads
        if (!is_null($lead->converted_at)) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Lead is already converted.');
        }
        
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        $companies = Company::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Leads/Convert', [
            'lead' => $lead,
            'users' => $users,
            'companies' => $companies,
            'pipelineStages' => ['Qualification', 'Needs Analysis', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'],
        ]);
    }

    /**
     * Convert a lead to a contact and optionally a deal.
     */
    public function convert(Request $request, Lead $lead)
    {
        // Don't allow converting already converted leads
        if (!is_null($lead->converted_at)) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Lead is already converted.');
        }
        
        $validated = $request->validate([
            'create_contact' => 'required|boolean',
            'contact' => 'required_if:create_contact,true|array',
            'contact.first_name' => 'required_if:create_contact,true|string|max:255',
            'contact.last_name' => 'required_if:create_contact,true|string|max:255',
            'contact.email' => 'nullable|email|max:255',
            'contact.phone' => 'nullable|string|max:20',
            'contact.job_title' => 'nullable|string|max:255',
            'contact.company_id' => 'nullable|exists:companies,id',
            'contact.owner_id' => 'nullable|exists:users,id',
            
            'create_deal' => 'required|boolean',
            'deal' => 'required_if:create_deal,true|array',
            'deal.name' => 'required_if:create_deal,true|string|max:255',
            'deal.amount' => 'nullable|numeric',
            'deal.pipeline_stage' => 'required_if:create_deal,true|string|max:255',
            'deal.expected_close_date' => 'nullable|date',
            'deal.probability' => 'nullable|integer|min:0|max:100',
            'deal.owner_id' => 'nullable|exists:users,id',
        ]);
        
        // Begin transaction for converting the lead
        \DB::beginTransaction();
        
        try {
            $contactId = null;
            $dealId = null;
            
            // Create contact if requested
            if ($validated['create_contact']) {
                $contactData = $validated['contact'];
                $contactData['created_by'] = Auth::id();
                $contactData['lead_source'] = $lead->lead_source;
                $contactData['lead_status'] = $lead->lead_status;
                $contactData['description'] = $lead->description;
                
                $contact = Contact::create($contactData);
                $contactId = $contact->id;
            }
            
            // Create deal if requested
            if ($validated['create_deal']) {
                $dealData = $validated['deal'];
                $dealData['created_by'] = Auth::id();
                $dealData['company_id'] = $validated['contact']['company_id'] ?? null;
                $dealData['primary_contact_id'] = $contactId;
                
                $deal = Deal::create($dealData);
                $dealId = $deal->id;
                
                // Associate the deal with the contact if both were created
                if ($contactId && $dealId) {
                    $deal->contacts()->attach($contactId, ['role' => 'Primary']);
                }
            }
            
            // Mark the lead as converted
            $lead->converted_at = now();
            $lead->converted_to_contact_id = $contactId;
            $lead->converted_to_deal_id = $dealId;
            $lead->save();
            
            \DB::commit();
            
            return redirect()->route('leads.show', $lead)
                ->with('success', 'Lead converted successfully.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to convert lead: ' . $e->getMessage());
        }
    }
    
/**
 * Add a note to the lead.
 */
public function addNote(Request $request, Lead $lead)
{
    // $this->authorize('edit-leads');
    
    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'content' => 'required|string',
    ]);
    
    $validated['created_by'] = Auth::id();
    
    $note = $lead->notes()->create($validated);
    
    return redirect()->back()->with('success', 'Note added successfully.');
}

  

   
}