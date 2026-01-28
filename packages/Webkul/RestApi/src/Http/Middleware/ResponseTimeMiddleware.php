<?php

namespace Webkul\RestApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseTimeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Засекаем время начала выполнения запроса
        $startTime = microtime(true);

        // Выполняем запрос
        $response = $next($request);

        // Вычисляем время выполнения в миллисекундах
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // Добавляем заголовок с временем выполнения
        if ($response instanceof Response) {
            $response->headers->set('X-Response-Time', $executionTime . 'ms');
        }

        return $response;
    }
}
