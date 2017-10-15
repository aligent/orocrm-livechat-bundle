<?php

namespace Aligent\LiveChatBundle\Tests\Functional\Service\API\Client;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Aligent\LiveChatBundle\Service\API\Client\Visitor;
use Aligent\LiveChatBundle\Service\Webhook\ChatException;
use Aligent\LiveChatBundle\Service\Webhook\ChatStart;
use Aligent\LiveChatBundle\Tests\Functional\DataFixtures\LoadContactEnhancedEntities;

/**
 * Unit tests for Visitor service
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class VisitorTest extends WebTestCase {

    /** @var  Visitor */
    protected $visitorApiService;

    protected function setUp() {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures([
            'Aligent\LiveChatBundle\Tests\Functional\DataFixtures\LoadContactEnhancedEntities',
        ]);

        $this->visitorApiService = $this->getContainer()->get('livechat.api_client_visitor');
        $this->router = $this->getContainer()->get('router');
    }


    /**
     * Simple smoke test to ensure the setApiCredentials function populates the
     * correct properties in the right order.
     */
    public function testSetApiCredentials() {
        $this->visitorApiService->setApiCredentials('foo', 'bar');
        $this->assertEquals('foo', $this->visitorApiService->licenseId);
        $this->assertEquals('bar', $this->visitorApiService->token);
    }


    /**
     * Test that the correct data is extrracted from contacts
     *
     * @dataProvider contactProvider
     * @param $contactReference string Reference used for featching fixture contact
     * @param $expectedName string The name we expect to see in the Oro link
     * @param $expectedExtraFields array Expected field values
     */
    public function testBuildRequestData($contactReference, $expectedName, $expectedExtraFields) {
        $contact = $this->getReference($contactReference);

        $apiData = $this->visitorApiService
           ->setApiCredentials('license', 'token')
           ->buildRequestData($contact);

        $this->assertEquals('license', $apiData['license_id']);
        $this->assertEquals('token', $apiData['token']);

        $this->assertArrayHasKey('fields', $apiData);
        $this->assertArrayHasKey(0, $apiData['fields']);

        $oroLinkField = array_shift($apiData['fields']);
        $contactUrl = $this->router->generate('oro_contact_view', ['id' => $contact->getId()], true);

        $this->assertEquals('OroCRM Contact', $oroLinkField['name']);
        $this->assertEquals($expectedName, $oroLinkField['value']);
        $this->assertEquals($contactUrl, $oroLinkField['url']);

        $this->assertEquals($expectedExtraFields, $apiData['fields']);
    }


    /**
     * Data provider for contact data.
     *
     * @return array
     */
    public function contactProvider() {
        return [
            [ // Test a contact with all required fields
                'SplContact_' . LoadContactEnhancedEntities::FIRST_ENTITY_NAME,
                'Mr Jonathan Day',
                [
                    [
                        'name' => 'Primary Phone',
                        'value' => '0412 345 678',
                    ]
                ]
            ],

            [ // Test a contact with a complete name but no phone
                'SplContact_' . LoadContactEnhancedEntities::SECOND_ENTITY_NAME,
                'Mr Jim Robert O\'Halloran',
                []
            ],

            [ // Test a contact with firstname/lastname only
                'SplContact_' . LoadContactEnhancedEntities::THIRD_ENTITY_NAME,
                'Swapna Paliniswamy',
                []
            ],

        ];
    }
}