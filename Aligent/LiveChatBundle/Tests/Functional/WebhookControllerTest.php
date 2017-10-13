<?php

namespace Aligent\LiveChatBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Controller functional tests
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class WebhookControllerTest extends WebTestCase {

    /**
     * Validate that chat_start webhook enforces HTTP Basic auth
     */
    public function testChatStartEnforcesHttpBasicAuth() {
        $client = static::createClient();
        $client->setServerParameter('HTTPS', true);
        $client->request('POST', '/livechatinc/webhook/chatStart');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }


    /**
     * Validate that chat_start webhook enforces HTTP Basic auth
     */
    public function testChatEndEnforcesHttpBasicAuth() {
        $client = static::createClient();
        $client->setServerParameter('HTTPS', true);
        $client->request('POST', '/livechatinc/webhook/chatEnd');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
