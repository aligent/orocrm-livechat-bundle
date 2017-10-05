<?php

namespace Aligent\LiveChatBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

/**
 * System Configuration listener
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */
class ConfigListener {
    protected $encryptor;

    public function __construct(Mcrypt $encryptor) {
        $this->encryptor = $encryptor;
    }

    /**
     * @param ConfigSettingsUpdateEvent $event
     */
    public function onBeforeSave(ConfigSettingsUpdateEvent $event) {
        $settings = $event->getSettings();

        if (isset($settings['aligent_live_chat.webhook_password'])) {
            $value = $settings['aligent_live_chat.webhook_password']['value'];
            $settings['aligent_live_chat.webhook_password']['value'] = $this->encryptor->encryptData($value);
        }

        $event->setSettings($settings);
    }
}