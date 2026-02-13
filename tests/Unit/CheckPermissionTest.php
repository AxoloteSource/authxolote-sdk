<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CheckPermissionTest extends TestCase
{
    /** @test */
    public function it_can_check_if_a_permission_is_allowed()
    {
        Authxolote::actionsFake(['create-post']);

        $result = Authxolote::action('create-post')->isAllow();

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_check_if_a_permission_is_not_allowed()
    {
        Authxolote::actionsFake(['create-post']);

        $result = Authxolote::action('delete-post')->isAllow();

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_check_if_multiple_permissions_are_allowed()
    {
        Authxolote::actionsFake(['create-post', 'edit-post']);

        $result = Authxolote::actions(['create-post', 'edit-post'])->isAllowAll();

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_check_if_multiple_permissions_are_not_all_allowed()
    {
        Authxolote::actionsFake(['create-post']);

        $result = Authxolote::actions(['create-post', 'edit-post'])->isAllowAll();

        $this->assertFalse($result);
    }
}
