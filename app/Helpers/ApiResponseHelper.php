<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponseHelper
{

    public const STATUS_OK = 200;
    public const STATUS_CREATED = 201;
    public const STATUS_ACCEPTED = 202;
    public const STATUS_NO_CONTENT = 204;

    public const STATUS_BAD_REQUEST = 400;
    public const STATUS_UNAUTHORIZED = 401;
    public const STATUS_FORBIDDEN = 403;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_UNPROCESSABLE = 422;

    public const STATUS_INTERNAL_SERVER_ERROR = 500;
    public const STATUS_SERVICE_UNAVAILABLE = 503;


    public static function success(string $message, $data = null, int $status = self::STATUS_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error(string $message, int $status = self::STATUS_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }


    public static function notFound(string $message = 'Not Found'): JsonResponse
    {
        return self::error($message, self::STATUS_NOT_FOUND);
    }

        // ✅ Unauthorized Response
        public static function unauthorized(string $message = 'You must be logged in to access this resource.'): JsonResponse
        {
            return self::error($message, self::STATUS_UNAUTHORIZED);
        }



    // ✅ Validation Error
    public static function validationError(array $errors, string $message = 'Validation Error'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], self::STATUS_UNPROCESSABLE);
    }

    // ✅ Paginated Response

    public static function send(bool $success, string $message, $data = null, int $status = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function paginated(bool $success, string $message, LengthAwarePaginator $paginator, $resourceClass = null, int $status = 200)
    {
        
        $items = $resourceClass
            ? $resourceClass::collection($paginator->items())
            : $paginator->items();

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'data'         => $items,
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ]
        ], $status);
    }

}
