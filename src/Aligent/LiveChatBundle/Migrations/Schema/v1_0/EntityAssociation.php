<?php

namespace Aligent\LiveChatBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

/**
 * Entity Relationship Migration
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class EntityAssociation implements Migration, OrderedMigrationInterface, ActivityExtensionAwareInterface {

    /** @var ActivityExtension */
    protected $activityExtension;


    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 2;
    }


    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension) {
        $this->activityExtension = $activityExtension;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries) {
        $this->activityExtension->addActivityAssociation($schema, 'aligent_lc_chattranscript', 'orocrm_contact', true);
        $this->activityExtension->addActivityAssociation($schema, 'aligent_lc_chattranscript', 'oro_user', true);
    }

}