<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function paginatedResponse($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if(!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

     protected function createdResponse($data, string $message = 'Created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }
}