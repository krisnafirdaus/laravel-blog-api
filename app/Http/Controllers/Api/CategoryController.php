<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    // GET /api/v1/categories
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Include posts count
        $query->withCount('posts');

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination atau all
        if ($request->boolean('all')) {
            $categories = $query->get();
            return $this->successResponse($categories, 'Daftar semua kategori');
        }

        $categories = $query->paginate($request->get('per_page', 10));
        return $this->paginatedResponse($categories, 'Daftar kategori');
    }

    // POST /api/v1/categories
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated);
        return $this->createdResponse($category, 'Kategori berhasil dibuat');
    }

    // GET /api/v1/categories/{id}
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('posts');
        return $this->successResponse($category, 'Detail kategori');
    }

    // PUT/PATCH /api/v1/categories/{id}
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);
        return $this->successResponse($category, 'Kategori berhasil diupdate');
    }

    // DELETE /api/v1/categories/{id}
    public function destroy(Category $category): JsonResponse
    {
        // Cek apakah kategori memiliki posts
        if ($category->posts()->count() > 0) {
            return $this->errorResponse(
                'Kategori tidak dapat dihapus karena masih memiliki posts', 
                422
            );
        }

        $categoryName = $category->name;
        $category->delete();
        
        return $this->successResponse(null, "Kategori '{$categoryName}' berhasil dihapus");
    }

    // GET /api/v1/categories/{id}/posts (Nested Resource)
    public function posts(Category $category, Request $request): JsonResponse
    {
        $query = $category->posts();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: hanya published
            $query->published();
        }

        // Sorting
        $query->orderBy('published_at', 'desc');
        
        $posts = $query->paginate($request->get('per_page', 10));
        
        return $this->paginatedResponse(
            $posts, 
            "Daftar posts dalam kategori '{$category->name}'"
        );
    }
}