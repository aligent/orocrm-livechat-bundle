<?php

namespace Aligent\LiveChatBundle\Service\API\Client;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Service class for accessing "visitor" API
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class Visitor {

    const API_URL = "https://api.livechatinc.com/visitors/{{visitor_id}}/details";
    const API_VERSION_HEADER = 'X-API-Version';
    const API_VERSION_NUMBER = 2;

    /** @var LoggerInterface  */
    protected $logger;

    /** @var JsonEncoder  */
    protected $jsonEncoder;

    /** @var Client  */
    protected $guzzleClient;

    /** @var  Router */
    protected $router;

    /** @var  Translator */
    protected $translator;

    // Public only for visibility from unit tests
    public $licenseId = false;
    public $token = false;

    public function __construct(LoggerInterface $logger, JsonEncoder $jsonEncoder, Client $guzzleClient, Router $router, TranslatorInterface $translator) {
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;
        $this->guzzleClient = $guzzleClient;
        $this->router = $router;
        $this->translator = $translator;

        $this->logger->debug("LiveChat API Client \"Visitor\" service initialized.");
    }


    /**
     * Assign the API credentials used to make this request.
     *
     * @param $licenseId string Live Chat Inc Licence Id
     * @param $token string API token
     * @return $this
     */
    public function setApiCredentials($licenseId, $token) {
        $this->licenseId = $licenseId;
        $this->token = $token;

        return $this;
    }


    public function sendVisitorApi(Contact $contact, $visitorId) {
        if ($this->licenseId == false || $this->token == false) {  // Loose comparison so that '' acts as if no creds are set.
            $this->logger->info("Not sending visitor API call, no API credentials available.");
        }

        $this->logger->info("Sending visitor details for ".$contact->getEmail()." ...");

        $requestParams = $this->buildRequestData($contact);
        $this->postToVisitorApi($requestParams, $visitorId);

    }

    protected function postToVisitorApi($requestParams, $visitorId) {
        $requestUrl = str_replace('{{visitor_id}}', urlencode($visitorId), self::API_URL);
        $requestBody = $this->jsonEncoder->encode($requestParams, JsonEncoder::FORMAT);

        $this->logger->debug("Request to URL: ".$requestUrl."  Bodyfollows ", [$requestBody]);

        /** @var Response $response */
        $response = $this->guzzleClient->post($requestUrl)
            ->addHeader(self::API_VERSION_HEADER, self::API_VERSION_NUMBER)
            ->setBody($requestBody, 'application/json')
            ->send();

        $this->logger->info("Visitor call status: ".$response->getStatusCode());

    }

    public function buildRequestData(Contact $contact) {
        $fields = [
            [
                'name' => $this->translator->trans('aligent.livechat.visitor.contact.label'),
                'value' => (string) $contact,  // Casting a contact to a string returns it's full name
                'url' => $this->router->generate('oro_contact_view', ['id' => $contact->getId()], true)
            ]
        ];

        if ($contact->getPrimaryPhone() !== null) {
            $fields[] = [
                'name' => $this->translator->trans('aligent.livechat.visitor.phone.label'),
                'value' => (string) $contact->getPrimaryPhone(), // ContactPhone object casts to string as the phone number
            ];
        }

        return [
            'license_id' => $this->licenseId,
            'token' => $this->token,
            'id' => $this->translator->trans('aligent.livechat.visitor.system.name'),

            // Do not enter "http" prefix in the icon URL.
            // Your server must be able to serve the icon
            // using both https:// and http:// protocols.
            'icon' => '//www.orocrm.com/favicon.ico',

            'fields' => $fields,

        ];
    }
}