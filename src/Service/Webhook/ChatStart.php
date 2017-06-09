<?php

namespace Aligent\LiveChatBundle\Service\Webhook;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Psr\Log\LoggerInterface;
use Aligent\LiveChatBundle\Service\API\Client\Visitor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;


/**
 * Service class for handling the "chat_start" webhook.
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class ChatStart extends ChatEventAbstract {

    /** @var Visitor  */
    protected $visitorApi;

    // Properties to store the information we need from the Webhook request
    public $visitorId = null;
    public $apiLicenseId = null;
    public $apiToken = null;

    const EVENT_TYPE = 'chat_started';

    public function __construct(LoggerInterface $logger, JsonEncoder $jsonEncoder, ApiEntityManager $contactManager, Visitor $visitor) {
        $this->visitorApi = $visitor;
        parent::__construct($logger, $jsonEncoder, $contactManager);

        $this->logger->debug("Webhook ChatStart service initialized.");
    }


    /**
     * Process the chatStart webhook request, pinging the LiveChat "visitors" API
     * if the customer we're chatting with is not a guest.
     *
     * @param $jsonString string JSON string from request body.
     */
    public function handleRequest($jsonString) {
        $chatData = $this->parseChatWebhook($jsonString);

        $contact = $this->getContactFromChatEvent($this->visitorEmail);
        if ($contact !== null) {
            $this->logger->info("Sending visitor API call for ". $contact->getEmail());

            $this->visitorApi
                ->setApiCredentials($this->apiLicenseId, $this->apiToken)
                ->sendVisitorApi($contact, $this->visitorId);
        } else {
            $this->logger->info("No contact record found for email: ". $this->visitorEmail);
        }
    }


    /**
     * Extract the required fields from the chatStart request payload.
     *
     * @param $jsonString
     * @throws ChatException
     * @return array Parsed JSON data
     */
    public function parseChatWebhook($jsonString) {
        $jsonData = parent::parseChatWebhook($jsonString);

        if (isset($jsonData['visitor']['id']) &&
            isset($jsonData['license_id']) &&
            isset($jsonData['token'])
        ) {
            $this->visitorId = $jsonData['visitor']['id'];
            $this->apiLicenseId = $jsonData['license_id'];
            $this->apiToken = $jsonData['token'];
        } else {
            $this->logger->error("Malformed chatStart webhook.  One or more required data fields missing.");
            throw new ChatException("Malformed chatStart webhook.  One or more required data fields missing.");
        }

        return $jsonData;
    }

}