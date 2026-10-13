<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'en';
        $hasSession = $request->hasSession();

        if ($request->filled('locale')) {
            $candidate = strtolower((string) $request->input('locale'));
            if (in_array($candidate, ['ar', 'en'], true)) {
                $locale = $candidate;
                if ($hasSession) {
                    $request->session()->put('locale', $locale);
                }
            }
        } elseif ($hasSession && $request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } elseif ($request->hasHeader('X-Locale')) {
            $candidate = strtolower((string) $request->header('X-Locale'));
            if (in_array($candidate, ['ar', 'en'], true)) {
                $locale = $candidate;
                if ($hasSession) {
                    $request->session()->put('locale', $locale);
                }
            }
        } elseif ($request->hasHeader('Accept-Language')) {
            $header = strtolower((string) $request->header('Accept-Language'));
            if (str_contains($header, 'ar')) {
                $locale = 'ar';
                if ($hasSession) {
                    $request->session()->put('locale', $locale);
                }
            }
        } elseif ($request->user() && ! empty($request->user()->preferred_languages)) {
            $preferred = $request->user()->preferred_languages;
            if (is_array($preferred) && in_array('ar', $preferred, true)) {
                $locale = 'ar';
                if ($hasSession) {
                    $request->session()->put('locale', $locale);
                }
            }
        }

        app()->setLocale($locale);

        $response = $next($request);

        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
