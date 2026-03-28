<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;
    
   // GET /api/v1/categories
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // search by name
        if($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // include posts count
        $query->withCount('posts');

        // sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // paginate atau all
        if($request->boolean('all')){
            $categories = $query->get();
            return $this->successResponse($categories, 'Dafatr semua ketagori');
        }

        $categories = $query->paginate($request->get('per_page', 10));
        return $this->successResponse($categories, 'Daftar kategori');
    }

    // POST /api/v1/categories
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        // jika slug tidak diisi, generate otomatis dari name
        if(empty($validated['slug'])){
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated);
        return $this->createdResponse($category, 'Kategori berhasil dibuat');
    }

   // get detail /api/v1/categories/{id}
    public function show(Category $category): JsonResponse
    {
        $category->load('posts');
        return $this->successResponse($category, 'Detail kategori');
    }

    // put/patch /api/v1/categories/{id}
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        // jika slug tidak diisi, generate otomatis dari name
        if(empty($validated['slug'])){
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);
        return $this->successResponse($category, 'Kategori berhasil diperbarui');
    }

    // delete /api/v1/categories/{id}
    public function destroy(Category $category): JsonResponse
    {
        // cek apakah kategori memiliki post
        if($category->posts()->count() > 0){
            return $this->errorResponse('Kategori tidak bisa dihapus karena memiliki post', 400); 
        }

        $categoryName = $category->name;
        $category->delete();

        return $this->successResponse(null, "Kategori '$categoryName' berhasil dihapus");
    }
}
