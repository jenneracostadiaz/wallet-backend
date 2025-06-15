<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = auth()->user()
            ->categories()
            ->with('subcategories')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        $this->authorize('view', $category);

        return new CategoryResource($category->load('subcategories'));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = auth()->user()->categories()->create([
            ...$request->validated(),
            'order' => $this->getNextOrder(),
        ]);

        return (new CategoryResource($category->load('subcategories')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        //
    }

    public function destroy(Category $category)
    {
        //
    }

    private function getNextOrder(): int
    {
        return auth()->user()
            ->categories()
            ->max('order') + 1;
    }
}
