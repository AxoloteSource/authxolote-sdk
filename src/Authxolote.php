<?php

namespace Authxolote\Sdk;

use Authxolote\Sdk\Clases\AttachRolesAction;
use Authxolote\Sdk\Clases\CheckPermission;
use Authxolote\Sdk\Clases\CreateOtp;
use Authxolote\Sdk\Clases\Me;
use Authxolote\Sdk\Clases\PasswordChange;
use Authxolote\Sdk\Clases\PasswordRecovery;
use Authxolote\Sdk\Clases\PasswordReset;
use Authxolote\Sdk\Clases\Register;
use Authxolote\Sdk\Clases\SetupMenu;
use Authxolote\Sdk\Clases\UserList;
use Authxolote\Sdk\Clases\ValidateCredentials;
use Authxolote\Sdk\Clases\VerifyOtp;
use Authxolote\Sdk\DTO\ExternalUser;
use Authxolote\Sdk\DTO\PasswordResetDto;
use Authxolote\Sdk\DTO\PasswordTokenDto;
use Authxolote\Sdk\DTO\UserDto;
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

    /**
     * Respuestas fake personalizadas por clase del SDK.
     *
     * @var array<class-string, array<string, mixed>>
     */
    private static array $fakeResponses = [];

    public static function isFake(): bool
    {
        return self::$isFake;
    }

    public static function fake(bool $isFake = true): void
    {
        self::$isFake = $isFake;
        self::$fakeResponses = [];
    }

    /**
     * Define una respuesta fake para una clase específica del SDK.
     *
     * @param  class-string  $class
     * @param  array<string, mixed>  $response
     */
    public static function fakeResponse(string $class, array $response): void
    {
        self::$fakeResponses[$class] = $response;
    }

    /**
     * Obtiene la respuesta fake configurada para una clase del SDK.
     *
     * @param  class-string  $class
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    public static function getFakeResponse(string $class, array $default = []): array
    {
        return self::$fakeResponses[$class] ?? $default;
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
     * Devuelve una instancia de Register para crear usuarios y consultar errores.
     */
    public static function signUp(): Register
    {
        return new Register;
    }

    /**
     * Devuelve una instancia de CreateOtp para crear retos OTP.
     */
    public static function createOtp(): CreateOtp
    {
        return new CreateOtp;
    }

    /**
     * Devuelve una instancia de VerifyOtp para validar retos OTP.
     */
    public static function verifyOtp(): VerifyOtp
    {
        return new VerifyOtp;
    }

    /**
     * Retorna una instancia de Me, clase que retorna datos del usuario autenticado
     */
    public static function me(): Me
    {
        return new Me;
    }

    /**
     * Devuelve una instancia de PasswordRecovery para consultar errores.
     */
    public static function passwordRecovery(): PasswordRecovery
    {
        return new PasswordRecovery;
    }

    /**
     * Devuelve una instancia de PasswordChange para consultar errores.
     */
    public static function passwordChange(): PasswordChange
    {
        return new PasswordChange;
    }

    /**
     * Devuelve una instancia de PasswordReset para consultar errores.
     */
    public static function passwordReset(): PasswordReset
    {
        return new PasswordReset;
    }

    /**
     * Devuelve una instancia de ValidateCredentials para validar credenciales sin generar un token.
     */
    public static function validateCredentials(): ValidateCredentials
    {
        return new ValidateCredentials;
    }

    public static function userList(): UserList
    {
        return new UserList;
    }

    /**
     * Devuelve una instancia de AttachRolesAction para consultar errores.
     */
    public static function attachRoles(array $roles): AttachRolesAction
    {
        return new AttachRolesAction($roles);
    }

    /**
     * Devuelve una instancia de SetupMenu para consultar errores.
     */
    public static function setupMenuAction(array $menu): SetupMenu
    {
        return new SetupMenu($menu);
    }

    /*************************/
    /*   DEPRECATE METHODS */
    /*************************/

    /**
     * @deprecated Usa passwordRecovery()->run($email) en su lugar.
     */
    public static function recoveryPassword(string $email): ?PasswordTokenDto
    {
        return self::passwordRecovery()->run($email);
    }

    /**
     * @deprecated Usa passwordChange()->run() en su lugar.
     */
    public static function changePassword(): ?PasswordTokenDto
    {
        return self::passwordChange()->run();
    }

    /**
     * @deprecated Usa passwordReset()->run(...) en su lugar.
     */
    public static function resetPassword(string $token, string $otp_code, string $password, string $password_confirmation): ?PasswordResetDto
    {
        return self::passwordReset()->run($token, $otp_code, $password, $password_confirmation);
    }

    /**
     * @deprecated Usa attachRoles($roles)->run() en su lugar.
     */
    public static function attachRolesAction(array $roles): bool
    {
        return self::attachRoles($roles)->run();
    }

    /**
     * @deprecated Usa setupMenuAction($menu)->run() en su lugar.
     */
    public static function setupMenu(array $menu): bool
    {
        return self::setupMenuAction($menu)->run();
    }

    /**
     * @deprecated Usa signUp()->signUp() en su lugar.
     */
    public static function register(string $email, string $name, string $password, string $roleKey): ?UserDto
    {
        return (new Register)->signUp($email, $name, $password, $roleKey);
    }
}
