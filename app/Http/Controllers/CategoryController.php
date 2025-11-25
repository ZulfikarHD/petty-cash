<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('view-categories');

        $categories = Category::query()
            ->withCount('transactions')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->input('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                if ($request->input('status') === 'active') {
                    $query->active();
                } else {
                    $query->inactive();
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create-categories');

        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'color' => $request->color ?? '#6366f1',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
        $this->authorize('view-categories');

        $category->load(['transactions' => function ($query) {
            $query->with('user')
                ->latest('transaction_date')
                ->latest('created_at')
                ->take(10);
        }]);

        // Get transaction statistics for this category
        $stats = [
            'total_transactions' => $category->transactions()->count(),
            'total_in' => $category->transactions()->where('type', 'in')->sum('amount'),
            'total_out' => $category->transactions()->where('type', 'out')->sum('amount'),
        ];

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        $this->authorize('edit-categories');

        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'color' => $request->color ?? $category->color,
            'is_active' => $request->boolean('is_active', $category->is_active),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('delete-categories');

        // Check if category has transactions
        if ($category->transactions()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing transactions.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
