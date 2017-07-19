<?php

namespace Aligent\LiveChatBundle\Controller;

use Aligent\LiveChatBundle\Service\Webhook\ChatException;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * Webhook Controller
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 *
 * @Route("/webhook")
 */
class WebhookController extends Controller {

    const WEBHOOK_USERNAME = 'livechatinc';

    /**
     * @Route("/chatStart")
     * @return Response
     */
    public function chatStartAction() {
        if (!$this->hasHttpBasicAuthentication()) {
            return new Response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic realm="LiveChat Webhooks"'] );
        }

        try {
            $this->get('livechat.webhook_chatstart')->handleRequest($this->getRequest()->getContent());
            return new Response("Success", 200);
        } catch (ChatException $e) {
            return new Response($e->getMessage(), 500);
        }
    }


    /**
     * @Route("/chatEnd")
     * @return Response
     */
    public function chatEndAction() {
        if (!$this->hasHttpBasicAuthentication()) {
            return new Response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic realm="LiveChat Webhooks"'] );
        }

        try {
            $this->get('livechat.webhook_chatend')->handleRequest($this->getRequest()->getContent());
            return new Response("Success", 200);
        } catch (ChatException $e) {
            return new Response($e->getMessage(), 500);
        }
    }


    /**
     * This is totally messed up.  No one should ever do HTTP Basic Authentication
     * this way.  Given the constraints of Nexcess (bless 'em) not giving us access
     * to the vhost (and we can't do what we need to do via .htaccess), Oro/Symfony not
     * supporting basic auth on PHP-FPM, and LiveChat Inc not supporting WSSE
     * authentication this is all we're left with.  Welcome to my world...
     *
     * @return bool True if successfully authenticated
     */
    protected function hasHttpBasicAuthentication() {
        $this->phpFpmAuthWorkaround();

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ) {
            return false;
        } else {
            if ($_SERVER['PHP_AUTH_USER'] == self::WEBHOOK_USERNAME &&
                $_SERVER['PHP_AUTH_PW'] == $this->getWebhookPassword()) {
                return true;
            }
        }
        return false;
    }


    /**
     * Fetches the current password from system config
     * and decrypts it using
     *
     * @return string Password for webhook authentication
     */
    protected function getWebhookPassword() {
        $config = $this->get('oro_config.user');
        $password = $config->get('aligent_live_chat.webhook_password');
        /** @var Mcrypt $encryptor */
        $encryptor = $this->get('oro_security.encoder.mcrypt');
        return $encryptor->decryptData($password);
    }

    /**
     * Manually process the $_SERVER['Authenticated'] superglobal into username
     * and password because PHP-FPM.
     */
    protected function phpFpmAuthWorkaround() {
        if (!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['Authorization'])) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['Authorization'], 6)));

            if (strlen($_SERVER['PHP_AUTH_USER']) == 0 || strlen($_SERVER['PHP_AUTH_PW']) == 0) {
                unset($_SERVER['PHP_AUTH_USER']);
                unset($_SERVER['PHP_AUTH_PW']);
            }
        }
    }
}
