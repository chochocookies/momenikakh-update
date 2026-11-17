<?php

// Tests for App\Http\Middleware\AddSurfaceHeader (Task 6.4)
// Validates Requirement 5.4: surface identifier header is added to responses.

use App\Http\Middleware\AddSurfaceHeader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('middleware adds X-Surface header with the given surface name', function () {
    $middleware = new AddSurfaceHeader();
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => new Response('ok'), 'marketing');

    expect($response->headers->get('X-Surface'))->toBe('marketing');
});

test('middleware passes each valid surface name through correctly', function (string $surface) {
    $middleware = new AddSurfaceHeader();
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => new Response('ok'), $surface);

    expect($response->headers->get('X-Surface'))->toBe($surface);
})->with(['marketing', 'app', 'public']);

test('middleware does not alter the response body', function () {
    $middleware = new AddSurfaceHeader();
    $request = Request::create('/');
    $body = 'Hello Surface';

    $response = $middleware->handle($request, fn () => new Response($body), 'app');

    expect($response->getContent())->toBe($body);
});

test('middleware calls next handler in the pipeline', function () {
    $middleware = new AddSurfaceHeader();
    $request = Request::create('/');
    $called = false;

    $middleware->handle($request, function () use (&$called) {
        $called = true;

        return new Response('ok');
    }, 'public');

    expect($called)->toBeTrue();
});
