<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use ApiResponse;

    // GET /api/v1/posts
    public function index(Request $request): JsonResponse
    {
        // Eager load category untuk menghindari N+1
        $query = Post::with('category');

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: hanya published untuk public API
            $query->published();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $posts = $query->paginate($request->get('per_page', 10));

        // Transform data untuk menambahkan excerpt
        $transformedPosts = collect($posts->items())->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'status' => $post->status,
                'published_at' => $post->published_at,
                'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                ] : null,
                'created_at' => $post->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar posts',
            'data' => $transformedPosts,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    // POST /api/v1/posts
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|min:5|max:255',
            'slug' => 'nullable|string|unique:posts,slug',
            'content' => 'required|string|min:50',
            'status' => 'sometimes|in:draft,published',
        ], [
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak ditemukan',
            'title.required' => 'Judul wajib diisi',
            'title.min' => 'Judul minimal 5 karakter',
            'content.required' => 'Konten wajib diisi',
            'content.min' => 'Konten minimal 50 karakter',
        ]);

        // Auto-generate slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set published_at jika status published
        if (isset($validated['status']) && $validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);
        $post->load('category');

        return $this->createdResponse($post, 'Post berhasil ditambahkan');
    }

    // GET /api/v1/posts/{id}
    public function show(Post $post): JsonResponse
    {
        $post->load('category');

        return $this->successResponse([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'excerpt' => $post->excerpt,
            'status' => $post->status,
            'published_at' => $post->published_at,
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
                'slug' => $post->category->slug,
            ] : null,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
        ], 'Detail post');
    }

    // PUT/PATCH /api/v1/posts/{id}
    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|min:5|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . $post->id,
            'content' => 'sometimes|string|min:50',
            'status' => 'sometimes|in:draft,published',
        ]);

        if (isset($validated['title']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Update published_at jika publish draft
        if (isset($validated['status']) && $validated['status'] === 'published' && $post->status === 'draft') {
            $validated['published_at'] = now();
        }

        $post->update($validated);
        $post->load('category');

        return $this->successResponse($post, 'Post berhasil diupdate');
    }

    // DELETE /api/v1/posts/{id}
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();
        return $this->successResponse(null, 'Post berhasil dihapus');
    }

    // Custom: Publish post
    public function publish(Post $post): JsonResponse
    {
        $post->publish();
        return $this->successResponse($post, 'Post berhasil dipublish');
    }
}