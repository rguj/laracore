<?php

namespace Rguj\Laracore\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class BaseHandler extends ExceptionHandler
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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    final public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    final public function render($request, Throwable $exception)
    {        
        $canIgnite = webclient_is_dev() || cuser_is_admin();
        config()->set('app.debug', $canIgnite);

        if($canIgnite) app('debugbar')->enable();
        else app('debugbar')->disable();

        if ($this->isHttpException($exception)) {
            /** @var \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e */
            $e = $exception;
            $code = $e->getStatusCode();
            $title = view_title('Error '.$code, true);
            $message = str_sanitize($e->getMessage());
            $message = !empty($message) ? $message : STATIC::__HTTP_STATUS__[$code] ?? 'Unknown Error';
            $up = url_parse(request()->fullUrl());
            $data = ['code'=>$code, 'title'=>$title, 'message'=>$message, 'pathQueryFragment' => (string)$up->pathQueryFragment];
            return response()->view('errors.error', $data, $code);
        } else {
            // dump(get_class($exception));
            $error_classes = ['Exception', 'ParseError', 'Error'];
            if(!$canIgnite && in_array(get_class($exception), $error_classes)) {
                abort(500);  // if not admin, recall itself as HttpException
            }
        }
        return parent::render($request, $exception);
    }



    # CUSTOM

    const __HTTP_STATUS__ = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        300 => 'Multiple Choice',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'unused',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];







}
