<?php

namespace Aligent\LiveChatBundle\Tests\Unit\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;

class LoadContactEnhancedEntities extends AbstractFixture
{
    const FIRST_ENTITY_NAME  = 'Jonathan';
    const SECOND_ENTITY_NAME = 'Jim';
    const THIRD_ENTITY_NAME  = 'Swapna';

    public static $owner = 'admin';

    /**
     * @var array
     */
    protected $contactsData = [
        [
            'prefix'    => 'Mr',
            'firstName' => self::FIRST_ENTITY_NAME,
            'middleName' => '',
            'lastName'  => 'Day',
            'phone'     => '0412 345 678',
        ],
        [
            'prefix'    => 'Mr',
            'firstName' => self::SECOND_ENTITY_NAME,
            'middleName' => 'Robert',
            'lastName'  => 'O\'Halloran',
            'phone'     => '',
        ],
        [
            'prefix'    => '',
            'firstName' => self::THIRD_ENTITY_NAME,
            'middleName' => '',
            'lastName'  => 'Paliniswamy',
            'phone'     => '',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('OroUserBundle:User')->findOneByUsername(self::$owner);
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->contactsData as $contactData) {
            $contact = new Contact();
            $contact->setOwner($user);
            $contact->setOrganization($organization);
            $contact->setNamePrefix($contactData['prefix']);
            $contact->setFirstName($contactData['firstName']);
            $contact->setMiddleName($contactData['middleName']);
            $contact->setLastName($contactData['lastName']);
            if ($contactData['phone'] != '') {
                $contactPhone = new ContactPhone();
                $contactPhone->setPrimary(true);
                $contactPhone->setPhone($contactData['phone']);

                $contact->addPhone($contactPhone);
            }
            $this->setReference('SplContact_' . $contactData['firstName'], $contact);
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
