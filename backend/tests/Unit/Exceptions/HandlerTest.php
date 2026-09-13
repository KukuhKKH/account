<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use App\Exceptions\User\UserNotFoundException;
use Exception;
use Hyperf\Context\ResponseContext;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Server\ResponsePlusProxy;
use Hypervel\Auth\Access\AuthorizationException;
use Hypervel\Auth\AuthenticationException;
use Hypervel\Database\Eloquent\ModelNotFoundException;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Validator;
use Hypervel\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * @internal
 */
class HandlerTest extends TestCase
{
    protected Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->app->make(Handler::class);

        $response = new ResponsePlusProxy(new Response());
        ResponseContext::set($response);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createApiRequest(): Request
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('path')->andReturn('api/v1/users');
        $request->shouldReceive('expectsJson')->andReturn(true);

        return $request;
    }

    public function testRenderUserDomainException(): void
    {
        $request   = $this->createApiRequest();
        $exception = new UserNotFoundException('Pengguna tidak ditemukan.');

        $response = $this->handler->render($request, $exception);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('USER_NOT_FOUND', $body['code']);
        $this->assertSame('Pengguna tidak ditemukan.', $body['message']);
    }

    public function testRenderAuthenticationException(): void
    {
        $request   = $this->createApiRequest();
        $exception = new AuthenticationException('Unauthenticated.');

        $response = $this->handler->render($request, $exception);

        $this->assertSame(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('UNAUTHENTICATED', $body['code']);
    }

    public function testRenderAuthorizationException(): void
    {
        $request   = $this->createApiRequest();
        $exception = new AuthorizationException('Akses ditolak.');

        $response = $this->handler->render($request, $exception);

        $this->assertSame(403, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('ACCESS_DENIED', $body['code']);
        $this->assertNotEmpty($body['message']);
    }

    public function testRenderModelNotFoundException(): void
    {
        $request   = $this->createApiRequest();
        $exception = new ModelNotFoundException();

        $response = $this->handler->render($request, $exception);

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertContains($body['code'], ['USER_NOT_FOUND', 'NOT_FOUND']);
    }

    public function testRenderValidationException(): void
    {
        $request   = $this->createApiRequest();
        $validator = Validator::make(['email' => 'invalid'], ['email' => 'email|required']);
        $exception = new ValidationException($validator);

        $response = $this->handler->render($request, $exception);

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('VALIDATION_ERROR', $body['code']);
        $this->assertArrayHasKey('errors', $body);
    }

    public function testRenderUnhandledExceptionSanitized(): void
    {
        $request   = $this->createApiRequest();
        $exception = new Exception('SELECT * FROM secret_table WHERE password = "leak"');

        $response = $this->handler->render($request, $exception);

        $this->assertSame(500, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertSame('INTERNAL_SERVER_ERROR', $body['code']);
        $this->assertSame('Terjadi kesalahan pada server.', $body['message']);
        $this->assertStringNotContainsString('secret_table', (string) $response->getBody());
    }

    public function testRenderHttpExceptionsStatusCodes(): void
    {
        $request = $this->createApiRequest();

        // 401
        $e401 = new \Hyperf\HttpMessage\Exception\HttpException(401);
        $res401 = $this->handler->render($request, $e401);
        $this->assertSame(401, $res401->getStatusCode());
        $body401 = json_decode((string) $res401->getBody(), true);
        $this->assertSame('UNAUTHENTICATED', $body401['code']);

        // 403
        $e403 = new \Hyperf\HttpMessage\Exception\HttpException(403);
        $res403 = $this->handler->render($request, $e403);
        $this->assertSame(403, $res403->getStatusCode());
        $body403 = json_decode((string) $res403->getBody(), true);
        $this->assertSame('ACCESS_DENIED', $body403['code']);

        // 404
        $e404 = new \Hyperf\HttpMessage\Exception\HttpException(404);
        $res404 = $this->handler->render($request, $e404);
        $this->assertSame(404, $res404->getStatusCode());
        $body404 = json_decode((string) $res404->getBody(), true);
        $this->assertSame('NOT_FOUND', $body404['code']);

        // 422
        $e422 = new \Hyperf\HttpMessage\Exception\HttpException(422);
        $res422 = $this->handler->render($request, $e422);
        $this->assertSame(422, $res422->getStatusCode());
        $body422 = json_decode((string) $res422->getBody(), true);
        $this->assertSame('VALIDATION_ERROR', $body422['code']);
    }
}
