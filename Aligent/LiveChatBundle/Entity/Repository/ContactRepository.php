<?php

namespace Aligent\LiveChatBundle\Entity\Repository;

use Doctrine\ORM\NoResultException;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * Contact Repository
 *
 * Repository clas to wrap fetching contacts by email address.
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
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