<?php

namespace Authxolote\Sdk;

use Authxolote\Sdk\Clases\AttachRolesAction;
use Authxolote\Sdk\Clases\CheckPermission;
use Authxolote\Sdk\Clases\Me;
use Authxolote\Sdk\Clases\Register;
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

    public static function attachRolesAction(array $roles): bool
    {
        $action = new AttachRolesAction($roles);
        return $action->run();
    }
}
