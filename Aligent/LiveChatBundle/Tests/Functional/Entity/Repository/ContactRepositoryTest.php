<?php

namespace Aligent\LiveChatBundle\Tests\Functional\Entity\Repository;

use Aligent\LiveChatBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ContactRepositoryTest extends WebTestCase {

    /** @var  ContactRepository */
    protected $contactRepository;

    protected function setUp() {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures([
            'Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData',
        ]);

        $this->contactRepository = $this->getContainer()->get('livechat.repository.contact');
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
        $actualContact = $this->contactRepository->getContactForEmail($chatEmail);
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