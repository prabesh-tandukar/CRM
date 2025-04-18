<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request)
    {
        // Check permission
        // $this->authorize('view-contacts');
        
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $sortField = $request->input('sortField', 'created_at');
        $sortDirection = $request->input('sortDirection', 'desc');
        $companyId = $request->input('company_id');
        
        $query = Contact::with(['company', 'owner', 'tags'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($companyId, function ($query, $companyId) {
                $query->where('company_id', $companyId);
            });
            
        // Only show contacts owned by user unless they can see all
        if (!Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('Manager')) {
            $query->where('owner_id', Auth::id());
        }
        
        $contacts = $query->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->appends($request->all());
            
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        
        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'companies' => $companies,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
                'companyId' => $companyId,
            ],
            'can' => [
                'create' => Auth::user()->can('create-contacts') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'edit' => Auth::user()->can('edit-contacts') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-contacts') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        // $this->authorize('create-contacts');
        
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $users = Auth::user()->hasRole('Admin') 
            ? \App\Models\User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        return Inertia::render('Contacts/Create', [
            'companies' => $companies,
            'users' => $users,
            'leadSources' => ['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'],
            'leadStatuses' => ['New', 'Contacted', 'Qualified', 'Unqualified'],
        ]);
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request)
    {
        // $this->authorize('create-contacts');
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'owner_id' => 'nullable|exists:users,id',
            'lead_source' => 'nullable|string|max:255',
            'lead_status' => 'nullable|string|max:255',
            'mailing_address' => 'nullable|string',
            'is_primary_contact' => 'boolean',
            'description' => 'nullable|string',
        ]);
        
        // Set the creator
        $validated['created_by'] = Auth::id();
        
        // Set the owner to current user if not specified
        if (!isset($validated['owner_id'])) {
            $validated['owner_id'] = Auth::id();
        }
        
        $contact = Contact::create($validated);
        
        // Set as primary contact if flagged and has company
        if ($validated['is_primary_contact'] && $contact->company_id) {
            $contact->setAsPrimaryContact();
        }
        
        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact)
    {
        // $this->authorize('view-contacts');
        
        $contact->load([
            'company',
            'owner',
            'tags',
            'notes' => function ($query) {
                $query->with('creator')->latest();
            },
            'activities' => function ($query) {
                $query->with('owner', 'creator')->latest();
            },
            'tasks' => function ($query) {
                $query->with('assignee')->latest();
            },
            'deals' => function ($query) {
                $query->with('owner')->latest();
            },
        ]);
        
        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
            'can' => [
                'edit' => Auth::user()->can('edit-contacts') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-contacts') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact)
    {
        // $this->authorize('edit-contacts');
        
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $users = Auth::user()->hasRole('Admin') 
            ? \App\Models\User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'companies' => $companies,
            'users' => $users,
            'leadSources' => ['Website', 'Referral', 'Social Media', 'Email', 'Call', 'Other'],
            'leadStatuses' => ['New', 'Contacted', 'Qualified', 'Unqualified'],
        ]);
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        // $this->authorize('edit-contacts');
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'owner_id' => 'nullable|exists:users,id',
            'lead_source' => 'nullable|string|max:255',
            'lead_status' => 'nullable|string|max:255',
            'mailing_address' => 'nullable|string',
            'is_primary_contact' => 'boolean',
            'description' => 'nullable|string',
        ]);
        
        $contact->update($validated);
        
        // Update primary contact status if needed
        if ($validated['is_primary_contact'] && $contact->company_id) {
            $contact->setAsPrimaryContact();
        }
        
        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact)
    {
        // $this->authorize('delete-contacts');
        
        $contact->delete();
        
        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
    
    /**
     * Import contacts from a CSV file.
     */
    public function import(Request $request)
    {
        // $this->authorize('import-contacts');
        
        // Implement CSV import logic
        
        return redirect()->route('contacts.index')
            ->with('success', 'Contacts imported successfully.');
    }
    
    /**
     * Export contacts to a CSV file.
     */
    public function export(Request $request)
    {
        // $this->authorize('export-contacts');
        
        // Implement CSV export logic
        
        return redirect()->route('contacts.index')
            ->with('success', 'Contacts exported successfully.');
    }

    /**
     * Add a note to the contact.
     */
    public function addNote(Request $request, Contact $contact)
    {
        // $this->authorize('edit-contacts');
        
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        
        $validated['created_by'] = Auth::id();
        
        $note = $contact->notes()->create($validated);
        
        return redirect()->back()->with('success', 'Note added successfully.');
    }
    }