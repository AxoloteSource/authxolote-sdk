<?php

namespace Authxolote\Sdk;

use Authxolote\Sdk\Clases\AttachRolesAction;
use Authxolote\Sdk\Clases\SetupMenu;
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
     * Devuelve una instancia de Register para crear usuarios y consultar errores.
     */
    public static function signUp(): Register
    {
        return new Register;
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
    /*   DEPRECATE METHODS   */
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
