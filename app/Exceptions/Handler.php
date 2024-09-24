<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Auth;
use Session;
use Illuminate\Support\Facades\Log;
use Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use App\Mail\ExceptionOccured;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        TokenMismatchException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        Log::error($e);
        if($e instanceof \Illuminate\Session\TokenMismatchException){
            return redirect()
                ->route("logout"); 
            Session::flash("flash_message",'Your session expired!');
        }
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)
        {
            Log::error('NotFoundHttpException Route: ' . $request->url() );
        }

        if (!$this->shouldntReport($e)) {
            $this->sendEmail($e, $request); // sends an email
        }
        return redirect('/error');
        return parent::render($request, $e);
    }

    public function sendEmail(Exception $exception, $request)
    {
        try {
            $e = FlattenException::create($exception);

            $handler = new SymfonyExceptionHandler();

            $html = $handler->getHtml($e);
            Mail::setSwiftMailer($this->getSwiftMailer());
            $html = $request->url() . $html;
            Mail::to('emem.isaac@abujaelectricity.com')->send(new ExceptionOccured($html));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public function getSwiftMailer(){
        $transport = \Swift_SmtpTransport::newInstance(
        \Config::get('mail.host'),
        \Config::get('mail.port'),
        \Config::get('mail.encryption'))
        ->setUsername(\Config::get('mail.username'))
        ->setPassword(\Config::get('mail.password'))
        ->setStreamOptions(['ssl' => \Config::get('mail.ssloptions')]);
    
        $mailer = \Swift_Mailer::newInstance($transport);
        return $mailer;
      }
}
