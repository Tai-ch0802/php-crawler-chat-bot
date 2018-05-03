<?php

namespace Tests\Unit;

use Taichuchu\AwsSms\Sms\Message;
use Taichuchu\AwsSms\Sms\Sms;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testAWS()
    {
        $this->markTestSkipped('OK!');
        /** @var Sms $sms */
        $sms = app(Sms::class);

        $message = new Message('test from laravel 8=D');

        $response = $sms->sendSms($message, '+886912345678');
    }
}
