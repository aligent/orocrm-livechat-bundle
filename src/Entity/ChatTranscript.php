<?php

namespace Aligent\LiveChatBundle\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Aligent\LiveChatBundle\Model\ExtendChatTranscript;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * Transcript Entity
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 *
 * @ORM\Table(
 *      name="aligent_lc_chattranscript",
 *      indexes={
 *          @ORM\Index(name="IDX_livechat_chat_id", columns={"livechat_chat_id"}),
 *      }
 * )
 * @ORM\Entity(repositoryClass="Aligent\LiveChatBundle\Entity\Repository\ChatTranscriptRepository")
 *
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-comments"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          }
 *      }
 * )
 */
class ChatTranscript extends ExtendChatTranscript implements CreatedAtAwareInterface {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_name", type="string", length=255)
     */
    protected $contactName;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="agent_name", type="string", length=255)
     */
    protected $agentName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="transcript", type="text")
     */
    protected $transcript;

    /**
     * @var string
     *
     * @ORM\Column(name="livechat_chat_id", type="string", length=255)
     */
    protected $livechatChatId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="chat_start", type="datetime")
     */
    protected $chatStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="chat_end", type="datetime")
     */
    protected $chatEnd;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Get entity created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }


    /**
     * Set entity created date/time
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null) {
        $this->createdAt = $createdAt;
    }


    /**
     * Set contact (Contact object representing the customer)
     *
     * @param Contact $contact
     * @return Call
     */
    public function setContact($contact) {
        $this->contact = $contact;

        return $this;
    }


    /**
     * Get contact (Contact object representing the customer)
     *
     * @return Contact
     */
    public function getContact() {
        return $this->contact;
    }


    /**
     * Get name of the contact (customer) with whom the chat was held.
     *
     * @return string
     */
    public function getContactName() {
        if ($this->getContact()) {
            return (string) $this->getContact();
        }
        return $this->contactName;
    }


    /**
     * Set name of the contact (customer) with whom the chat was held.
     *
     * @param string $contactName
     *
     * @return ChatTranscript
     */
    public function setContactName($contactName) {
        $this->contactName = $contactName;

        return $this;
    }


    /**
     * Set owner (user object representing the agent that handled the chat)
     *
     * @param User $owner
     * @return Call
     */
    public function setOwner($owner) {
        $this->owner = $owner;

        return $this;
    }


    /**
     * Get owner (user object representing the agent that handled the chat)
     *
     * @return User
     */
    public function getOwner() {
        return $this->owner;
    }


    /**
     * Get name of the agent (staff member) handling the chat
     *
     * @return string
     */
    public function getAgentName() {
        if ($this->getOwner()) {
            return $this->getOwner()->getFullName();
        }
        return $this->agentName;
    }


    /**
     * Set name of the agent (staff member) handling the chat
     *
     * @param string $agentName
     *
     * @return ChatTranscript
     */
    public function setAgentName($agentName) {
        $this->agentName = $agentName;

        return $this;
    }


    /**
     * Get contact (customer) email address
     *
     * @return string
     */
    public function getEmail() {
        if ($this->getContact()) {
            return (string) $this->getContact()->getEmail();
        }
        return $this->email;
    }


    /**
     * Set contact (customer) email address
     *
     * @param string $email
     *
     * @return ChatTranscript
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }


    /**
     * Get transcript
     *
     * @return string
     */
    public function getTranscript() {
        return $this->transcript;
    }


    /**
     * Set transcript
     *
     * @param string $transcript
     *
     * @return ChatTranscript
     */
    public function setTranscript($transcript) {
        $this->transcript = $transcript;

        return $this;
    }


    /**
     * Get LiveChat's Chat Id
     *
     * @return string
     */
    public function getLivechatChatId() {
        return $this->livechatChatId;
    }


    /**
     * Set LiveChat's Chat Id
     *
     * @param string $livechatChatId
     *
     * @return ChatTranscript
     */
    public function setLivechatChatId($livechatChatId) {
        $this->livechatChatId = $livechatChatId;

        return $this;
    }


    /**
     * Get chat started time
     *
     * @return \DateTime
     */
    public function getChatStart() {
        return $this->chatStart;
    }


    /**
     * Set chat started time
     *
     * @param string $chatStart
     *
     * @return ChatTranscript
     */
    public function setChatStart($chatStart) {
        $this->chatStart = $chatStart;

        return $this;
    }


    /**
     * Get chat end time
     *
     * @return \DateTime
     */
    public function getChatEnd() {
        return $this->chatEnd;
    }


    /**
     * Set chat end time
     *
     * @param string $chatEnd
     *
     * @return ChatTranscript
     */
    public function setChatEnd($chatEnd) {
        $this->chatEnd = $chatEnd;

        return $this;
    }


    /**
     * Set organization
     *
     * @param Organization $organization
     * @return Call
     */
    public function setOrganization(Organization $organization = null) {
        $this->organization = $organization;

        return $this;
    }


    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization() {
        return $this->organization;
    }


}