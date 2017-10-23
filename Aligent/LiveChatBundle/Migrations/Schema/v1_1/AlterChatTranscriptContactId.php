<?php

namespace Aligent\LiveChatBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Create Table Migration
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class AlterChatTranscriptContactId implements Migration, OrderedMigrationInterface {

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 1;
    }

    public function up(Schema $schema, QueryBag $queries) {
        $table = $schema->getTable('aligent_lc_chattranscript');
        $table->changeColumn('contact_id', ['notnull'  => 0]);
    }
}