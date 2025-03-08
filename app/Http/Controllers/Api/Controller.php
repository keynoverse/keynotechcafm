<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Facility Management System API",
 *     description="API documentation for the Facility Management System",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * 
 * @OA\Server(
 *     description="Local Environment",
 *     url=L5_SWAGGER_CONST_HOST
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Response(
 *     response="401",
 *     description="Unauthenticated",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Unauthenticated.")
 *     )
 * )
 * 
 * @OA\Response(
 *     response="403",
 *     description="Forbidden",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *     )
 * )
 * 
 * @OA\Response(
 *     response="404",
 *     description="Not Found",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Resource not found.")
 *     )
 * )
 * 
 * @OA\Response(
 *     response="422",
 *     description="Validation Error",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="The given data was invalid."),
 *         @OA\Property(
 *             property="errors",
 *             type="object",
 *             @OA\AdditionalProperties(
 *                 type="array",
 *                 @OA\Items(type="string")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Response(
 *     response="500",
 *     description="Server Error",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Internal server error.")
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
} 