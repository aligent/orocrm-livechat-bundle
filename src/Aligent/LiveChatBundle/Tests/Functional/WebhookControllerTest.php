<?php

namespace Aligent\LiveChatBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase {

    /**
     * Validate that chat_start webhook enforces HTTP Basic auth
     */
    public function testChatStartEnforcesHttpBasicAuth() {
        $client = static::createClient();
        $client->request('POST', '/livechatinc/webhook/chatStart');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }


    /**
     * Validate that chat_start webhook enforces HTTP Basic auth
     */
    public function testChatEndEnforcesHttpBasicAuth() {
        $client = static::createClient();
        $client->request('POST', '/livechatinc/webhook/chatEnd');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
