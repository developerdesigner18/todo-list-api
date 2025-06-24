<?php
namespace App\Http\Traits;

trait ResponseTrait
{
    function sendResponse($message,$data,$code = 200){
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data
        ],$code);
    }
    function sendErrorResponse($message,$data,$code = 400){
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data
        ],$code);
    }
    function sendExceptionResponse($exception,$code = 500){
        return response()->json([
            'status'  => false,
            'message' => 'Internal Server Error',
            'exception' => $exception
        ],$code);
    }
    function sendSuccess($message,$code = 200){
        return response()->json([
            'status'  => true,
            'message' => $message
        ],$code);
    }
    function sendError($message, $code = 400){
        return response()->json([
            'status'  => false,
            'message' => $message
        ],$code);
    }
    function sendValidationError($data){
        return response()->json([
            'status'  => false,
            'error'    => $data
        ],422);
    }
}











