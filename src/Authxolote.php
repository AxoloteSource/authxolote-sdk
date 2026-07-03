<?php

namespace Authxolote\Sdk;

use Authxolote\Sdk\Clases\AttachRolesAction;
use Authxolote\Sdk\Clases\CheckPermission;
use Authxolote\Sdk\Clases\Me;
use Authxolote\Sdk\Clases\PasswordChange;
use Authxolote\Sdk\Clases\PasswordRecovery;
use Authxolote\Sdk\Clases\PasswordReset;
use Authxolote\Sdk\Clases\Register;
use Authxolote\Sdk\Clases\UserList;
use Authxolote\Sdk\DTO\PasswordResetDto;
use Authxolote\Sdk\DTO\PasswordTokenDto;
use Authxolote\Sdk\DTO\UserDto;
use Authxolote\Sdk\DTO\ExternalUser;
use Authxolote\Sdk\Enums\RoleEnum;

class Authxolote
{
    public static function actingAsExternal(RoleEnum $role, array $attributes = []): ExternalUser
    {
        $user = ExternalUser::factory()->withRole($role)->create($attributes);
        self::actingAs($user);

        return $user;
    }

    public static function actionsFake(array $actions): void
    {
        CheckPermission::actionsFake($actions);
    }

    private static bool $isFake = false;

    public static function isFake(): bool
    {
        return self::$isFake;
    }

    public static function fake(bool $isFake = true): void
    {
        Authxolote::$isFake = $isFake;
    }

    public static function actingAs($user): void
    {
        $guard = app('auth')->guard('authxolote');
        $guard->setUser($user);
    }

    /**
     * Recibe una sola acción y devuelve una instancia de CheckPermission
     */
    public static function action(string $action): CheckPermission
    {
        return new CheckPermission([$action]);
    }

    /**
     * Recibe varias acciones y devuelve una instancia de CheckPermission
     */
    public static function actions(array $actions): CheckPermission
    {
        return new CheckPermission($actions);
    }

    /**
     * Registra un usuario con los detalles proporcionados y devuelve una instancia de UserDto.
     */
    public static function register(string $email, string $name, string $password, string $roleKey): ?UserDto
    {
        return (new Register)->register($email, $name, $password, $roleKey);
    }

    /**
     * Retorna una instancia de Me, clase que retorna datos del usuario autenticado
     */
    public static function me(): Me
    {
        return new Me;
    }

    /**
     * Inicia la recuperación de contraseña.
     */
    public static function recoveryPassword(string $email): ?PasswordTokenDto
    {
        return (new PasswordRecovery())->run($email);
    }

    /**
     * Inicia el cambio de contraseña.
     */
    public static function changePassword(): ?PasswordTokenDto
    {
        return (new PasswordChange())->run();
    }

    /**
     * Restablece la contraseña usando el OTP.
     */
    public static function resetPassword(string $token, string $otp_code, string $password, string $password_confirmation): ?PasswordResetDto
    {
        return (new PasswordReset())->run($token, $otp_code, $password, $password_confirmation);
    }

    public static function userList(): UserList
    {
        return new UserList;
    }

    public static function attachRolesAction(array $roles): bool
    {
        $action = new AttachRolesAction($roles);
        return $action->run();
    }
}
