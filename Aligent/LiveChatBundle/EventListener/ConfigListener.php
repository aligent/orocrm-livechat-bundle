<?php

namespace Aligent\LiveChatBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

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
    protected $encoder;

    public function __construct(BCryptPasswordEncoder $encoder) {
        $this->encoder = $encoder;
    }

    /**
     * @param ConfigSettingsUpdateEvent $event
     */
    public function onBeforeSave(ConfigSettingsUpdateEvent $event) {
        $settings = $event->getSettings();

        if (isset($settings['aligent_live_chat.webhook_password'])) {
            $value = $settings['aligent_live_chat.webhook_password']['value'];
            if ($value !== null) {
                $settings['aligent_live_chat.webhook_password']['value'] = $this->encoder->encodePassword($value, false);
            } else {
                unset($settings['aligent_live_chat.webhook_password']);
            }
        }

        $event->setSettings($settings);
    }
}