<?php

namespace App\Http\Middleware;

use App\Services\ClientService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class McuMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Session::get('client_id') && !\Session::get('contract_id')) {
            $clients = (new ClientService)->paginate(1000);
            $contracts =[];
            return response()->view('pages.user.mcu', compact('clients', 'contracts'));
        }
        return $next($request);
    }
}
