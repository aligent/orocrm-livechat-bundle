<?php
namespace Aligent\LiveChatBundle\DataTransfer;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Data Transfer Object for data parsed from Chat End webhook request
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
 * @method string getVisitorName()
 * @method string getVisitorEmail()
 * @method ChatEndData setChatId(string $value)
 * @method ChatEndData setChatStart(\DateTime $value)
 * @method ChatEndData setChatEnd(\DateTime $value)
 * @method ChatEndData setTranscript(string $value)
 * @method ChatEndData setAgents(array $value)
 * @method ChatEndData setOwner(User $value)
 * @method ChatEndData setAgentName(string $value)
 * @method ChatEndData setAgentEmail(string $value)
 * @method ChatEndData setVisitorName(string $value)
 * @method ChatEndData setVisitorEmail(string $value)
 */

class ChatEndData extends AbstractDTO {


}