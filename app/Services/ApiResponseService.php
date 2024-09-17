<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponseService
{

        /**
     * Returns a successful JSON response.
     *
     * @param mixed $data The data to include in the response.
     * @param string $message A success message.
     * @param int $status The HTTP status code.
     * @return \Illuminate\Http\JsonResponse
     */
   public static function success($data = null, $message = 'Operation Succefull', $status = 200)
   {
        return response()->json([
            "status"=> 'succses',
            "message"=> $message,
            "data"=> $data,
        ],$status);
   }
// =============================================================================================================
        /**
     * Returns an error JSON response.
     *
     * @param string $message The error message.
     * @param int $status The HTTP status code.
     * @param mixed $data Additional data to include in the response.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = 'Operation failed', $status = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

}
