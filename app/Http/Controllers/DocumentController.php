<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Deal;
use App\Models\Contact;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $type = $request->input('type', '');
        
        $query = Document::with(['uploaded_by', 'documentable'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('original_filename', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($type, function ($query, $type) {
                if ($type === 'deal') {
                    $query->where('documentable_type', 'App\\Models\\Deal');
                } elseif ($type === 'contact') {
                    $query->where('documentable_type', 'App\\Models\\Contact');
                } elseif ($type === 'company') {
                    $query->where('documentable_type', 'App\\Models\\Company');
                }
            });
        
        // Non-admin users can only see documents they uploaded or related to their deals/contacts
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            $query->where(function ($q) {
                $q->where('uploaded_by', Auth::id())
                  ->orWhereHas('documentable', function ($q) {
                      $q->where('owner_id', Auth::id());
                  });
            });
        }
        
        $documents = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->all());
            
        $categories = Document::distinct('category')->whereNotNull('category')->pluck('category');
        
        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'categories' => $categories,
            'typeOptions' => [
                'deal' => 'Deals',
                'contact' => 'Contacts',
                'company' => 'Companies',
            ],
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'category' => $category,
                'type' => $type,
            ],
            'can' => [
                'upload' => Auth::user()->can('upload-documents') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-documents') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for uploading a new document.
     */
    public function create(Request $request)
    {
        $dealId = $request->input('deal_id');
        $contactId = $request->input('contact_id');
        $companyId = $request->input('company_id');
        
        $relatedTo = null;
        
        if ($dealId) {
            $deal = Deal::find($dealId);
            $relatedTo = [
                'type' => 'deal',
                'id' => $deal->id,
                'name' => $deal->name
            ];
        } elseif ($contactId) {
            $contact = Contact::find($contactId);
            $relatedTo = [
                'type' => 'contact',
                'id' => $contact->id,
                'name' => $contact->first_name . ' ' . $contact->last_name
            ];
        } elseif ($companyId) {
            $company = Company::find($companyId);
            $relatedTo = [
                'type' => 'company',
                'id' => $company->id,
                'name' => $company->name
            ];
        }
        
        $categories = Document::distinct('category')->whereNotNull('category')->pluck('category');
        
        return Inertia::render('Documents/Create', [
            'relatedTo' => $relatedTo,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly uploaded document in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'documentable_type' => 'nullable|string|in:App\\Models\\Deal,App\\Models\\Contact,App\\Models\\Company',
            'documentable_id' => 'nullable|integer|required_with:documentable_type',
        ]);
        
        // Handle file upload
        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileType = $file->getMimeType();
        
        // Generate a unique filename
        $filename = uniqid() . '_' . $originalFilename;
        
        // Store the file
        $path = $file->storeAs('documents', $filename, 'public');
        
        // Create document record
        $document = new Document([
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'file_path' => $path,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'uploaded_by' => Auth::id(),
        ]);
        
        // Associate with related model if provided
        if (!empty($validated['documentable_type']) && !empty($validated['documentable_id'])) {
            $document->documentable_type = $validated['documentable_type'];
            $document->documentable_id = $validated['documentable_id'];
        }
        
        $document->save();
        
        // Determine redirect based on related model
        if (!empty($validated['documentable_type']) && !empty($validated['documentable_id'])) {
            $type = class_basename($validated['documentable_type']);
            $id = $validated['documentable_id'];
            
            $routeMap = [
                'Deal' => 'deals.show',
                'Contact' => 'contacts.show',
                'Company' => 'companies.show',
            ];
            
            if (isset($routeMap[$type])) {
                return redirect()->route($routeMap[$type], $id)
                    ->with('success', 'Document uploaded successfully.');
            }
        }
        
        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        $document->load(['uploaded_by', 'documentable']);
        
        return Inertia::render('Documents/Show', [
            'document' => $document,
            'downloadUrl' => route('documents.download', $document),
            'can' => [
                'delete' => Auth::user()->can('delete-documents') || 
                           Auth::user()->hasRole(['Admin', 'Manager']) ||
                           $document->uploaded_by === Auth::id(),
            ]
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        // Check if user has permission to download
        if (!Auth::user()->hasRole(['Admin', 'Manager']) && 
            $document->uploaded_by !== Auth::id() &&
            !$this->userCanAccessDocument($document)) {
            abort(403, 'Unauthorized action.');
        }
        
        return Storage::disk('public')->download($document->file_path, $document->original_filename);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        // Check if user has permission to delete
        if (!Auth::user()->hasRole(['Admin', 'Manager']) && $document->uploaded_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Delete the file
        Storage::disk('public')->delete($document->file_path);
        
        // Delete the record
        $document->delete();
        
        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
    
    /**
     * Check if user can access a document through related models.
     */
    private function userCanAccessDocument(Document $document)
    {
        if (!$document->documentable) {
            return false;
        }
        
        $userId = Auth::id();
        
        // Check if user owns the related model
        if (method_exists($document->documentable, 'owner') && 
            $document->documentable->owner_id === $userId) {
            return true;
        }
        
        return false;
    }

    /**
 * Show the form for editing the specified document.
 */
public function edit(Document $document)
{
    // Check if user has permission to edit
    if (!Auth::user()->hasRole(['Admin', 'Manager']) && $document->uploaded_by !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }
    
    $document->load(['uploaded_by', 'documentable']);
    
    $categories = Document::distinct('category')->whereNotNull('category')->pluck('category');
    
    return Inertia::render('Documents/Edit', [
        'document' => $document,
        'categories' => $categories,
        'can' => [
            'delete' => Auth::user()->can('delete-documents') || 
                       Auth::user()->hasRole(['Admin', 'Manager']) ||
                       $document->uploaded_by === Auth::id(),
        ]
    ]);
}

/**
 * Update the specified document in storage.
 */
public function update(Request $request, Document $document)
{
    // Check if user has permission to edit
    if (!Auth::user()->hasRole(['Admin', 'Manager']) && $document->uploaded_by !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }
    
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category' => 'nullable|string|max:255',
        'version' => 'nullable|string|max:50',
    ]);
    
    $document->update($validated);
    
    return redirect()->route('documents.show', $document)
        ->with('success', 'Document updated successfully.');
}
}