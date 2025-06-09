<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('categories/index', [
            'categories' => auth()->user()->categories()->whereNull('parent_id')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('categories/create',[
            'categories' => auth()->user()->categories()->whereNull('parent_id')->get(),
            'types' => [
                ['type' => 'income', 'name' => 'Income'],
                ['type' => 'expense', 'name' => 'Expense'],
                ['type' => 'transfer', 'name' => 'Transfer'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        auth()->user()->categories()->create([
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->parent_id ? $request->parent_id : null,
            'icon' => $request->icon,
            'color' => $request->color,
            'order' => auth()->user()->categories()->max('order') + 1,
        ]);
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return Inertia::render('categories/edit', [
            'category' => $category,
            'categories' => auth()->user()->categories()->whereNull('parent_id')->get(),
            'types' => [
                ['type' => 'income', 'name' => 'Income'],
                ['type' => 'expense', 'name' => 'Expense'],
                ['type' => 'transfer', 'name' => 'Transfer'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->parent_id ? $request->parent_id : null,
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
