<?php
namespace Aligent\LiveChatBundle\DataTransfer;

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
 * @method string getVisitorId()
 * @method string getVisitorEmail()
 * @method string getApiLicenseId()
 * @method string getApiToken()
 * @method ChatStartData setVisitorId(string $value)
 * @method ChatStartData setVisitorEmail(\DateTime $value)
 * @method ChatStartData setApiLicenseId(\DateTime $value)
 * @method ChatStartData setApiToken(string $value)
 */

class ChatStartData extends AbstractDTO {


}