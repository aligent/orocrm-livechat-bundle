<?php

namespace Aligent\LiveChatBundle\Service\Webhook;

use Doctrine\ORM\NoResultException;
use Oro\Bundle\DotmailerBundle\Entity\Contact;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Base class for Webhook Services
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
abstract class ChatEventAbstract {

    /** @var ApiEntityManager  */
    protected $contactManager;
    /** @var JsonEncoder  */
    protected $jsonEncoder;
    /** @var LoggerInterface  */
    protected $logger;


    // Properties to store the information we need from the Webhook request
    public $visitorEmail = null;

    const INDEX_EVENT_TYPE = 'event_type';

    public function __construct(LoggerInterface $logger, JsonEncoder $jsonEncoder, ApiEntityManager $contactManager) {
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;
        $this->contactManager = $contactManager;

        $this->logger->debug("Webhook ChatEnd service initialized.");
    }

    /**
     * Process the webhook request,
     *
     * @param $jsonString string JSON string from request body.
     */
    abstract public function handleRequest($jsonString);


    /**
     * Extract the required fields from the chat event request payload.
     *
     * @param $jsonString
     * @throws ChatException
     * @return array Parsed JSON data for further procesing in child classes
     */
    public function parseChatWebhook($jsonString) {
        $jsonData = $this->decodeAndValidateWebhook($jsonString);

        if (isset($jsonData['visitor']['email'])) {
            $this->visitorEmail = $jsonData['visitor']['email'];
        } else {
            $this->logger->error("Malformed chat webhook.  Email field is missing.");
            throw new ChatException("Malformed chat webhook.  Email field is missing.");
        }

        return $jsonData;
    }


    /**
     * Decode the webhook JSON and validate that event type exists and is correct.
     *
     * @param $jsonString string Raw JSON from request
     * @return array Deserialised webhook data
     * @throws ChatException
     */
    protected function decodeAndValidateWebhook($jsonString) {
        try {
            $jsonData = $this->jsonEncoder->decode($jsonString, 'json');
        } catch (UnexpectedValueException $e) {
            $this->logger->error("Could not decode webhook data.  Error: " . $e->getMessage());
            throw new ChatException("Could not decode webhook data.");
        }

        if (!array_key_exists(self::INDEX_EVENT_TYPE, $jsonData)) {
            $this->logger->error("Malformed chat webhook.  Missing event type.");
            throw new ChatException('Invalid event type');
        } elseif ($jsonData[self::INDEX_EVENT_TYPE] !== static::EVENT_TYPE) {
            $this->logger->error("Malformed chat webhook.  Received invalid event type '" . $jsonData[self::INDEX_EVENT_TYPE] . "'.");
            throw new ChatException('Invalid event type');
        }
        return $jsonData;
    }


    /**
     * Lookup contact based on email address.  Swallow the exception and return
     * a sentinel if not found (which is quite likely for guest chats).
     *
     * @param $email string Contact email address
     * @return null|Contact
     */
    public function getContactFromChatEvent($email) {
        $qb = $this->contactManager->getRepository()
            ->createQueryBuilder('c')
            ->join('c.emails', 'e');

        $qb->andWhere(
            $qb->expr()->like('e.email', ':query')
        )->setParameter('query', sprintf('%%%s%%', $email))
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