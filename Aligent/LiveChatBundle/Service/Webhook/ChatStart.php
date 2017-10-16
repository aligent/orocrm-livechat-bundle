<?php

namespace Aligent\LiveChatBundle\Service\Webhook;

use Aligent\LiveChatBundle\DataTransfer\AbstractDTO;
use Aligent\LiveChatBundle\DataTransfer\ChatStartData;
use Aligent\LiveChatBundle\Entity\Repository\ContactRepository;
use Aligent\LiveChatBundle\Exception\ChatException;
use Aligent\LiveChatBundle\Service\API\Client\Visitor;
use Psr\Log\LoggerInterface;
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

    const EVENT_TYPE = 'chat_started';

    public function __construct(LoggerInterface $logger,
                                JsonEncoder $jsonEncoder,
                                ContactRepository $contactRepo,
                                Visitor $visitor) {
        $this->visitorApi = $visitor;
        parent::__construct($logger, $jsonEncoder, $contactRepo);

        $this->logger->debug("Webhook ChatStart service initialized.");
    }


    /**
     * Process the chatStart webhook request, pinging the LiveChat "visitors" API
     * if the customer we're chatting with is not a guest.
     *
     * @param $jsonString string JSON string from request body.
     */
    public function handleRequest($jsonString) {
        $chatStartData = new ChatStartData();

        $this->parseChatWebhook($jsonString, $chatStartData);

        $contact = $this->contactRepo->getContactForEmail($chatStartData->getVisitorEmail());
        if ($contact !== null) {
            $this->logger->info("Sending visitor API call for ". $contact->getEmail());

            $this->visitorApi
                ->setApiCredentials($chatStartData->getApiLicenseId(), $chatStartData->getApiToken())
                ->sendVisitorApi($contact, $chatStartData->getVisitorId());
        } else {
            $this->logger->info("No contact record found for email: ". $chatStartData->getVisitorEmail());
        }
    }


    /**
     * Extract the required fields from the chatStart request payload.
     *
     * @param $jsonString
     * @throws array Parsed JSON data
     * @return ChatStartData A DTO containing the required information.
     */
    public function parseChatWebhook($jsonString, AbstractDTO $chatStartData) {
        $jsonData = parent::parseChatWebhook($jsonString, $chatStartData);

        if (isset($jsonData['visitor']['id']) &&
            isset($jsonData['license_id']) &&
            isset($jsonData['token'])
        ) {
            $chatStartData->setVisitorId($jsonData['visitor']['id']);
            $chatStartData->setApiLicenseId($jsonData['license_id']);
            $chatStartData->setApiToken($jsonData['token']);
        } else {
            $this->logger->error("Malformed chatStart webhook.  One or more required data fields missing.");
            throw new ChatException("Malformed chatStart webhook.  One or more required data fields missing.");
        }

        return $jsonData;
    }

}