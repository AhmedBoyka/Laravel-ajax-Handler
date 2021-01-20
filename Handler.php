<?php

namespace App\Exceptions;






use BadMethodCallException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use ParseErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;


class Handler extends ExceptionHandler
{


    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [

        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);

    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        
        $exceptionMessage=$exception->getMessage();
        $statutCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;//Internal server error code default Http Code
        $response['code'] = method_exists($exception, 'getCode') ? $exception->getCode() : $statutCode;//custom error code, default   will be same a statusCode
        $response['message'] =  'An error happened' ;//set by default to avoid showing sensitive data to user
        $response['status']=false;
        if ($exception instanceof QueryException)
            return $exception->render($request);

        else if ($exception instanceof TenantException)
            return $exception->render($request);

        else if ($exception instanceof TokenMismatchException) {
            $response['message'] = "Session Timeout try login again";
        }

        else if ($exception instanceof ParseErrorException) {
            $response['message'] = "Syntax Error";
        }
        else if ($exception instanceof BadMethodCallException) {
            $response['message'] = "Method called doesnt exist";
        }

        else if ($exception instanceof ValidationException) {
            $realMessage = print_r($e->validator->failed(), true);
            $response['message']  = "Error in your form filled";

        }
        //For Json Response ONLY
        if ($request->expectsJson())
            return response()->json($response, $statutCode);

        return parent::render($request, $exception);
    }
    /**
     * Convert an authentication exception into an unauthenticated response.
     * @param \Illuminate\Http\Request $request
     * @param AuthenticationException $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {


        if ($request->expectsJson()) {
            return response()->json(['errors' => ['status' => 401, 'message' => $exception->getMessage()]], 401);
        }
      
        return redirect()->guest(route('login'));


    }
}
