<?php

namespace Aligent\LiveChatBundle\Tests\Unit\Service\Webhook;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Aligent\LiveChatBundle\Service\API\Client\Visitor;
use Aligent\LiveChatBundle\Service\Webhook\ChatException;
use Aligent\LiveChatBundle\Service\Webhook\ChatStart;

class ChatStartTest extends WebTestCase {

    /** @var  \Aligent\LiveChatBundle\Service\Webhook\ChatStart */
    protected $chatStartService;

    protected function setUp() {
        $this->initClient();

        $this->loadFixtures([
            'Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData',
        ]);

        $this->chatStartService = $this->getContainer()->get('livechat.webhook_chatstart');
    }


    /**
     * Smoke test for the "happy path" should not throw exceptions, but no return
     * value means nothing to test or assert here other than that the API function
     * was called as exptected.  Other unit tests of specific methods provide more
     * detailed test coverage.
     */
    public function testHandleRequesthappyPath() {

        $visitorApiMock = $this->getMockBuilder(Visitor::class)
            ->disableOriginalConstructor()
            ->setMethods(['setApiCredentials', 'sendVisitorApi'])
            ->getMock();

        $visitorApiMock->expects($this->once())
            ->method('setApiCredentials')
            ->with($this->equalTo('8762851'), $this->equalTo('d31e8942603a5a8ca363dce9a8cff403'))
            ->willReturnSelf();

        $visitorApiMock->expects($this->once())
            ->method('sendVisitorApi');


        $mockedApiChatService = new ChatStart(
            $this->getContainer()->get('logger'),
            $this->getContainer()->get('serializer.encoder.json'),
            $this->getContainer()->get('oro_contact.contact.manager'),
            $visitorApiMock
        );

        $mockedApiChatService->handleRequest('{"event_type":"chat_started","event_unique_id":"c3b47e2f0525d0e669a0af23898050b8","token":"d31e8942603a5a8ca363dce9a8cff403","license_id":"8762851","chat":{"id":"OPNPVYYWEX","started_timestamp":1494390273,"url":"http://local.aligent.com/","messages":[{"user_type":"agent","author_name":"Jim O\'Halloran","agent_id":"test1@test.test","text":"Hello. How may I help you?","timestamp":1494390273}],"attachments":[],"events":[],"agents":[{"name":"Jim O\'Halloran","login":"test1@test.test"}],"tags":[],"groups":[0]},"visitor":{"id":"S1493177572.a78cd5f8c4","name":"Jim O\'Halloran","email":"test1@test.test","country":"Australia","city":"Adelaide","language":"en","page_current":"http://local.aligent.com/","timezone":"Australia/South"},"pre_chat_survey":[{"id":"2001","type":"name","label":"Name:","answer":"Jim O\'Halloran"},{"id":"2002","type":"email","label":"E-mail:","answer":"test1@test.test"}]}');
    }


    /**
     * Test that function to parse the necessary options out of the webhook payload
     * throws the appropriate exception when request is malformed in any way.
     *
     * @dataProvider malformedWebhookJson
     */
    public function testParseChatWebhookThrowsExceptionForMalformedRequests($jsonString) {
        $this->expectException(ChatException::class);
        $this->chatStartService->parseChatWebhook($jsonString);
    }


    /**
     * Various malformed requests for testing request parser.
     *
     * @return array
     */
    public function malformedWebhookJson() {
        return [
            ['{"event_type":"chat_ended"}'],  // Wrong event type
            ['{"event_type":""}'],            // Empty event type
            ['{}'],                           // Missing all fields but valid JSON
            ['foobar'],                       // Invalid JSON
            ['{"event_type":"chat_started","license_id":"8762851","visitor":{"id":"S1493177572.a78cd5f8c4","email":"test1@test.test"}}'],                               // Token missing
            ['{"event_type":"chat_started","token":"d31e8942603a5a8ca363dce9a8cff403","visitor":{"id":"S1493177572.a78cd5f8c4","email":"test1@test.test"}}'],           // License Id missing
            ['{"event_type":"chat_started","token":"d31e8942603a5a8ca363dce9a8cff403","license_id":"8762851"}'],                                                         // Visitor object missing
            ['{"event_type":"chat_started","token":"d31e8942603a5a8ca363dce9a8cff403","license_id":"8762851","visitor":{"id":"S1493177572.a78cd5f8c4"}}'],              // Has visitor missing email
            ['{"event_type":"chat_started","token":"d31e8942603a5a8ca363dce9a8cff403","license_id":"8762851","visitor":{"email":"test1@test.test"}}'],                  // Has visitor missing id
        ];
    }


    /**
     * Parse data from a valid request and assert that it was parsed correctly
     * and in full.
     */
    public function testParseChatStartWebhookParsesRequiredFields() {
        $this->chatStartService->parseChatWebhook('{"event_type":"chat_started","event_unique_id":"c3b47e2f0525d0e669a0af23898050b8","token":"d31e8942603a5a8ca363dce9a8cff403","license_id":"8762851","chat":{"id":"OPNPVYYWEX","started_timestamp":1494390273,"url":"http://local.aligent.com/","messages":[{"user_type":"agent","author_name":"Jim O\'Halloran","agent_id":"test1@test.test","text":"Hello. How may I help you?","timestamp":1494390273}],"attachments":[],"events":[],"agents":[{"name":"Jim O\'Halloran","login":"test1@test.test"}],"tags":[],"groups":[0]},"visitor":{"id":"S1493177572.a78cd5f8c4","name":"Jim O\'Halloran","email":"test1@test.test","country":"Australia","city":"Adelaide","language":"en","page_current":"http://local.aligent.com/","timezone":"Australia/South"},"pre_chat_survey":[{"id":"2001","type":"name","label":"Name:","answer":"Jim O\'Halloran"},{"id":"2002","type":"email","label":"E-mail:","answer":"test1@test.test"}]}');

        $this->assertEquals('test1@test.test', $this->chatStartService->visitorEmail);
        $this->assertEquals('S1493177572.a78cd5f8c4', $this->chatStartService->visitorId);
        $this->assertEquals('d31e8942603a5a8ca363dce9a8cff403', $this->chatStartService->apiToken);
        $this->assertEquals('8762851', $this->chatStartService->apiLicenseId);
    }


    /**
     * Test that the contact lookup function finds the correct contact, and returns
     * the appropriate sentinel when contact is not found.
     *
     * @dataProvider contactProvider
     *
     * @param $chatEmail string Email address to search for
     * @param $expectedContactReference null|string Contact expected to be found
     */
    public function testGetContactFromChatEvent($chatEmail, $expectedContactReference) {
        $actualContact = $this->chatStartService->getContactFromChatEvent($chatEmail);
        if ($expectedContactReference === null) {
            $this->assertNull($actualContact);
        } else {
            $expectedContact = $this->getReference($expectedContactReference);
            $this->assertEquals($expectedContact->getId(), $actualContact->getId());
        }
    }


    /**
     * Data provider for contact data.
     *
     * @return array
     */
    public function contactProvider() {
        return [
            ['test1@test.test', 'Contact_' . LoadContactEntitiesData::FIRST_ENTITY_NAME],
                                                // Valid contact from fixture data
            ['jim@aligent.com.au', null],       // Real person, just not in fixtures!
        ];
    }
}