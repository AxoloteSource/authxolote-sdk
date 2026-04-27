<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\App;

class TranslationTest extends TestCase
{
    /** @test */
    public function it_can_translate_messages_to_spanish()
    {
        App::setLocale('es');

        $this->assertEquals(
            'No tienes permiso para acceder a este recurso',
            __('You do not have permission to access this resource')
        );
    }

    /** @test */
    public function it_can_translate_messages_to_english()
    {
        App::setLocale('en');

        $this->assertEquals(
            'You do not have permission to access this resource',
            __('You do not have permission to access this resource')
        );
    }
}
