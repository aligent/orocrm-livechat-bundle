<?php

namespace Aligent\LiveChatBundle\Entity\Repository;

use Doctrine\ORM\NoResultException;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ContactRepository {

    /** @var ApiEntityManager  */
    protected $contactManager;


    public function __construct(ApiEntityManager $contactManager) {
        $this->contactManager = $contactManager;
    }

    /**
     * Lookup contact based on email address.  Swallow the exception and return
     * a sentinel if not found (which is quite likely for guest chats).
     *
     * @param $email string Contact email address
     * @return null|Contact
     */
    public function getContactForEmail($email) {
        $qb = $this->contactManager->getRepository()
            ->createQueryBuilder('c')
            ->join('c.emails', 'e');

        $qb->andWhere(
            $qb->expr()->eq('e.email', ':query')
        )->setParameter('query', $email)
            ->setMaxResults(1);

        try {
            $contact = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            // If not a known contact, then swallow the exception and return a
            // sentinel value instead.
            $contact = null;
        }
        return $contact;
    }

}