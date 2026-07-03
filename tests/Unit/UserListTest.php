<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\UserList;
use Authxolote\Sdk\DTO\UserListDto;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UserListTest extends TestCase
{
    /** @test */
    public function it_returns_an_instance_of_user_list()
    {
        $service = Authxolote::userList();

        $this->assertInstanceOf(UserList::class, $service);
    }

    /** @test */
    public function it_can_get_all_user_lists()
    {
        Authxolote::fake();

        $response = Authxolote::userList()->index();

        $this->assertIsArray($response);
        $this->assertEquals('OK', $response['status']);
        $this->assertArrayHasKey('data', $response);
    }

    /** @test */
    public function it_can_store_a_user_list()
    {
        Authxolote::fake();

        $dto = Authxolote::userList()->store('My List', 'my-list', 'A description');

        $this->assertInstanceOf(UserListDto::class, $dto);
        $this->assertEquals('My List', $dto->name);
    }

    /** @test */
    public function it_can_show_a_user_list()
    {
        Authxolote::fake();

        $dto = Authxolote::userList()->show('some-uuid');

        $this->assertInstanceOf(UserListDto::class, $dto);
        $this->assertNotEmpty($dto->id);
    }

    /** @test */
    public function it_can_update_a_user_list()
    {
        Authxolote::fake();

        $dto = Authxolote::userList()->update('some-uuid', 'Updated Name');

        $this->assertInstanceOf(UserListDto::class, $dto);
        $this->assertEquals('Updated Name', $dto->name);
    }

    /** @test */
    public function it_can_delete_a_user_list()
    {
        Authxolote::fake();

        $result = Authxolote::userList()->delete('some-uuid');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_users_from_a_user_list()
    {
        Authxolote::fake();

        $response = Authxolote::userList()->users('some-uuid');

        $this->assertIsArray($response);
        $this->assertEquals('OK', $response['status']);
        $this->assertArrayHasKey('data', $response);
    }

    /** @test */
    public function it_returns_null_when_index_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            'https://authxolote.test/api/api/v1/user-lists*' => Http::response(['message' => 'Error'], 422),
        ]);

        $response = Authxolote::userList()->index();

        $this->assertNull($response);
    }

    /** @test */
    public function it_returns_null_when_store_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            'https://authxolote.test/api/api/v1/user-lists' => Http::response(['message' => 'Error'], 422),
        ]);

        $dto = Authxolote::userList()->store('My List');

        $this->assertNull($dto);
    }

    /** @test */
    public function it_returns_null_when_show_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            'https://authxolote.test/api/api/v1/user-lists/some-uuid' => Http::response(['message' => 'Error'], 422),
        ]);

        $dto = Authxolote::userList()->show('some-uuid');

        $this->assertNull($dto);
    }

    /** @test */
    public function it_returns_null_when_update_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            'https://authxolote.test/api/api/v1/user-lists/some-uuid' => Http::response(['message' => 'Error'], 422),
        ]);

        $dto = Authxolote::userList()->update('some-uuid', 'Updated Name');

        $this->assertNull($dto);
    }

    /** @test */
    public function it_returns_false_when_delete_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            'https://authxolote.test/api/api/v1/user-lists/some-uuid' => Http::response(['message' => 'Error'], 422),
        ]);

        $result = Authxolote::userList()->delete('some-uuid');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_null_when_users_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            'https://authxolote.test/api/api/v1/user-lists/some-uuid/users*' => Http::response(['message' => 'Error'], 422),
        ]);

        $response = Authxolote::userList()->users('some-uuid');

        $this->assertNull($response);
    }
}
