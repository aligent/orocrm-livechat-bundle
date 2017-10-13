<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 12/10/17
 * Time: 4:20 PM
 */

namespace Aligent\LiveChatBundle\DataTransfer;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Abstract Data Transfer Object
 *
 * A simple, generic data transfer object (DTO), heavily inspired by Magento's
 * Varien_Object.
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 *
 * @method string getChatId()
 * @method \DateTime getChatStart()
 * @method \DateTime getChatEnd()
 * @method string getTranscript()
 * @method array getAgents()
 * @method User getOwner()
 * @method string getAgentName()
 * @method string getAgentEmail()
 * @method stringgetVisitorName()
 * @method ChatEndData setChatId(string $value)
 * @method ChatEndData setChatStart(\DateTime $value)
 * @method ChatEndData setChatEnd(\DateTime $value)
 * @method ChatEndData setTranscript(string $value)
 * @method ChatEndData setAgents(array $value)
 * @method ChatEndData setOwner(User $value)
 * @method ChatEndData setAgentName(string $value)
 * @method ChatEndData setAgentEmail(string $value)
 * @method ChatEndData setVisitorName(string $value)
 */

class ChatEndData extends AbstractDTO {


}