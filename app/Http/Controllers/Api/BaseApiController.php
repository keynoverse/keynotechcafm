<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ValidationException;

class BaseApiController extends Controller
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function createdResponse($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(string $message = 'Deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message, 204);
    }

    protected function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            422,
            $e->getErrors()
        );
    }
} 