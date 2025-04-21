<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Deal;
use App\Models\Contact;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $assigneeId = $request->input('assignee_id');
        $dueDate = $request->input('due_date');
        
        $query = Task::with(['assignee', 'creator', 'taskable'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($assigneeId, function ($query, $assigneeId) {
                $query->where('assignee_id', $assigneeId);
            })
            ->when($dueDate, function ($query, $dueDate) {
                if ($dueDate === 'today') {
                    $query->whereDate('due_date', today());
                } elseif ($dueDate === 'tomorrow') {
                    $query->whereDate('due_date', today()->addDay());
                } elseif ($dueDate === 'this_week') {
                    $query->whereBetween('due_date', [today(), today()->endOfWeek()]);
                } elseif ($dueDate === 'next_week') {
                    $query->whereBetween('due_date', [today()->next('Monday'), today()->next('Monday')->endOfWeek()]);
                } elseif ($dueDate === 'overdue') {
                    $query->whereDate('due_date', '<', today())->where('status', '!=', 'Completed');
                }
            });
            
        // Show only tasks assigned to user unless admin/manager
        if (!Auth::user()->hasRole(['Admin', 'Manager'])) {
            $query->where('assignee_id', Auth::id());
        }
        
        $tasks = $query->orderBy('due_date', 'asc')
            ->paginate($perPage)
            ->appends($request->all());
            
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'users' => $users,
            'statuses' => ['Not Started', 'In Progress', 'Completed', 'Deferred'],
            'dueDateOptions' => [
                'today' => 'Today',
                'tomorrow' => 'Tomorrow',
                'this_week' => 'This Week',
                'next_week' => 'Next Week',
                'overdue' => 'Overdue'
            ],
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'status' => $status,
                'assigneeId' => $assigneeId,
                'dueDate' => $dueDate,
            ],
            'stats' => [
                'total' => Task::count(),
                'completed' => Task::where('status', 'Completed')->count(),
                'overdue' => Task::whereDate('due_date', '<', today())->where('status', '!=', 'Completed')->count(),
                'today' => Task::whereDate('due_date', today())->count(),
            ],
            'can' => [
                'create' => Auth::user()->can('create-tasks') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'edit' => Auth::user()->can('edit-tasks') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-tasks') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request)
    {
        $dealId = $request->input('deal_id');
        $contactId = $request->input('contact_id');
        $companyId = $request->input('company_id');
        
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
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
        
        return Inertia::render('Tasks/Create', [
            'users' => $users,
            'statuses' => ['Not Started', 'In Progress', 'Completed', 'Deferred'],
            'priorities' => ['Low', 'Medium', 'High'],
            'relatedTo' => $relatedTo,
        ]);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Not Started,In Progress,Completed,Deferred',
            'assignee_id' => 'required|exists:users,id',
            'taskable_type' => 'nullable|string|in:App\\Models\\Deal,App\\Models\\Contact,App\\Models\\Company',
            'taskable_id' => 'nullable|integer|required_with:taskable_type',
        ]);
        
        // Set created_by
        $validated['created_by'] = Auth::id();
        
        // Set completed_at if status is Completed
        if ($validated['status'] === 'Completed') {
            $validated['completed_at'] = now();
        }
        
        $task = Task::create($validated);
        
        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $task->load(['assignee', 'creator', 'taskable']);
        
        return Inertia::render('Tasks/Show', [
            'task' => $task,
            'can' => [
                'edit' => Auth::user()->can('edit-tasks') || Auth::user()->hasRole(['Admin', 'Manager', 'Sales Rep']),
                'delete' => Auth::user()->can('delete-tasks') || Auth::user()->hasRole(['Admin', 'Manager']),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $task->load(['assignee', 'creator', 'taskable']);
        
        $users = Auth::user()->hasRole('Admin') 
            ? User::role(['Admin', 'Manager', 'Sales Rep'])->get(['id', 'name'])
            : [Auth::user()];
        
        $relatedTo = null;
        
        if ($task->taskable) {
            $taskableType = class_basename($task->taskable_type);
            
            $relatedTo = [
                'type' => strtolower($taskableType),
                'id' => $task->taskable->id,
                'name' => $taskableType === 'Contact' 
                    ? $task->taskable->first_name . ' ' . $task->taskable->last_name 
                    : $task->taskable->name
            ];
        }
        
        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'users' => $users,
            'statuses' => ['Not Started', 'In Progress', 'Completed', 'Deferred'],
            'priorities' => ['Low', 'Medium', 'High'],
            'relatedTo' => $relatedTo,
        ]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Not Started,In Progress,Completed,Deferred',
            'assignee_id' => 'required|exists:users,id',
        ]);
        
        // Set or clear completed_at based on status
        if ($validated['status'] === 'Completed' && !$task->completed_at) {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'Completed') {
            $validated['completed_at'] = null;
        }
        
        $task->update($validated);
        
        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Update task status quickly.
     */
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:Not Started,In Progress,Completed,Deferred',
        ]);
        
        $updateData = ['status' => $validated['status']];
        
        // Set or clear completed_at based on status
        if ($validated['status'] === 'Completed' && !$task->completed_at) {
            $updateData['completed_at'] = now();
        } elseif ($validated['status'] !== 'Completed') {
            $updateData['completed_at'] = null;
        }
        
        $task->update($updateData);
        
        return redirect()->back()->with('success', 'Task status updated.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        
        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}