<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\CheckPermission;
use Authxolote\Sdk\Clases\Me;
use Authxolote\Sdk\Tests\TestCase;

class AuthxoloteTest extends TestCase
{
    /** @test */
    public function it_can_be_set_to_fake_mode()
    {
        Authxolote::fake();
        $this->assertTrue(Authxolote::isFake());

        Authxolote::fake(false);
        $this->assertFalse(Authxolote::isFake());
    }

    /** @test */
    public function it_returns_an_instance_of_check_permission_on_action()
    {
        $action = Authxolote::action('create-post');
        $this->assertInstanceOf(CheckPermission::class, $action);
    }

    /** @test */
    public function it_returns_an_instance_of_check_permission_on_actions()
    {
        $actions = Authxolote::actions(['create-post', 'edit-post']);
        $this->assertInstanceOf(CheckPermission::class, $actions);
    }

    /** @test */
    public function it_returns_an_instance_of_me()
    {
        $me = Authxolote::me();
        $this->assertInstanceOf(Me::class, $me);
    }
}
