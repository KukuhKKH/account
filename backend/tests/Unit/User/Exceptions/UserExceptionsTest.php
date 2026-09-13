<?php

declare(strict_types=1);

namespace Tests\Unit\User\Exceptions;

use App\Exceptions\User\CannotDeactivateProtectedAccountException;
use App\Exceptions\User\CannotDeleteLastAdministratorException;
use App\Exceptions\User\CannotDeleteOwnAccountException;
use App\Exceptions\User\CannotResetOwnPasswordException;
use App\Exceptions\User\InvalidCurrentPasswordException;
use App\Exceptions\User\InvalidUserStatusTransitionException;
use App\Exceptions\User\UserAccessDeniedException;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserDomainException;
use App\Exceptions\User\UserNotFoundException;
use Tests\TestCase;

/**
 * @internal
 */
class UserExceptionsTest extends TestCase
{
    public function testUserNotFoundException(): void
    {
        $defaultException = new UserNotFoundException();
        $this->assertSame(404, $defaultException->getStatusCode());
        $this->assertSame('USER_NOT_FOUND', $defaultException->getErrorCode());
        $this->assertSame('Pengguna tidak ditemukan.', $defaultException->getMessage());
        $this->assertInstanceOf(UserDomainException::class, $defaultException);

        $customException = new UserNotFoundException('Custom not found');
        $this->assertSame('Custom not found', $customException->getMessage());
    }

    public function testUserAlreadyExistsException(): void
    {
        $defaultException = new UserAlreadyExistsException();
        $this->assertSame(409, $defaultException->getStatusCode());
        $this->assertSame('USER_ALREADY_EXISTS', $defaultException->getErrorCode());
        $this->assertSame('Alamat email sudah terdaftar dalam sistem.', $defaultException->getMessage());

        $customException = new UserAlreadyExistsException('Email terdaftar.');
        $this->assertSame('Email terdaftar.', $customException->getMessage());
    }

    public function testUserAccessDeniedException(): void
    {
        $defaultException = new UserAccessDeniedException();
        $this->assertSame(403, $defaultException->getStatusCode());
        $this->assertSame('ACCESS_DENIED', $defaultException->getErrorCode());
        $this->assertSame('Anda tidak memiliki hak akses untuk melakukan tindakan ini.', $defaultException->getMessage());

        $customException = new UserAccessDeniedException('Dilarang.');
        $this->assertSame('Dilarang.', $customException->getMessage());
    }

    public function testCannotDeleteOwnAccountException(): void
    {
        $defaultException = new CannotDeleteOwnAccountException();
        $this->assertSame(422, $defaultException->getStatusCode());
        $this->assertSame('CANNOT_DELETE_SELF', $defaultException->getErrorCode());
        $this->assertSame('Anda tidak dapat menghapus akun Anda sendiri.', $defaultException->getMessage());

        $customException = new CannotDeleteOwnAccountException('Anti self-harm.');
        $this->assertSame('Anti self-harm.', $customException->getMessage());
    }

    public function testCannotDeleteLastAdministratorException(): void
    {
        $defaultException = new CannotDeleteLastAdministratorException();
        $this->assertSame(422, $defaultException->getStatusCode());
        $this->assertSame('CANNOT_DELETE_LAST_ADMIN', $defaultException->getErrorCode());
        $this->assertSame('Tidak dapat menghapus Administrator atau Superadmin terakhir demi kestabilan sistem.', $defaultException->getMessage());

        $customException = new CannotDeleteLastAdministratorException('Cluster guard.');
        $this->assertSame('Cluster guard.', $customException->getMessage());
    }

    public function testCannotDeactivateProtectedAccountException(): void
    {
        $defaultException = new CannotDeactivateProtectedAccountException();
        $this->assertSame(422, $defaultException->getStatusCode());
        $this->assertSame('CANNOT_DEACTIVATE_PROTECTED_ACCOUNT', $defaultException->getErrorCode());
        $this->assertSame('Akun Superadmin tidak dapat dinonaktifkan demi kestabilan cluster.', $defaultException->getMessage());

        $customException = new CannotDeactivateProtectedAccountException('Protected account.');
        $this->assertSame('Protected account.', $customException->getMessage());
    }

    public function testInvalidUserStatusTransitionException(): void
    {
        $defaultException = new InvalidUserStatusTransitionException();
        $this->assertSame(422, $defaultException->getStatusCode());
        $this->assertSame('INVALID_STATUS_TRANSITION', $defaultException->getErrorCode());
        $this->assertSame('Transisi status akun tidak valid atau tidak diizinkan.', $defaultException->getMessage());

        $customException = new InvalidUserStatusTransitionException('Invalid state.');
        $this->assertSame('Invalid state.', $customException->getMessage());
    }

    public function testCannotResetOwnPasswordException(): void
    {
        $defaultException = new CannotResetOwnPasswordException();
        $this->assertSame(403, $defaultException->getStatusCode());
        $this->assertSame('CANNOT_RESET_OWN_PASSWORD', $defaultException->getErrorCode());
        $this->assertStringContainsString('Profil Pengguna & Kunci Keamanan', $defaultException->getMessage());

        $customException = new CannotResetOwnPasswordException('Custom self reset error.');
        $this->assertSame('Custom self reset error.', $customException->getMessage());
    }

    public function testInvalidCurrentPasswordException(): void
    {
        $defaultException = new InvalidCurrentPasswordException();
        $this->assertSame(422, $defaultException->getStatusCode());
        $this->assertSame('INVALID_CURRENT_PASSWORD', $defaultException->getErrorCode());
        $this->assertSame('Kata sandi lama yang Anda masukkan salah.', $defaultException->getMessage());

        $customException = new InvalidCurrentPasswordException('Wrong current password.');
        $this->assertSame('Wrong current password.', $customException->getMessage());
    }
}
