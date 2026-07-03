<?php

namespace App\Support;

class ApiResponse
{
    /**
     * Return a successful response.
     *
     * @param mixed $data
     * @param string $message
     * @param mixed|null $meta
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = [], $message = 'OK', $meta = null, $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed|null $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
