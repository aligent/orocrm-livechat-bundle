<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 16/10/17
 * Time: 12:02 PM
 */

namespace Aligent\LiveChatBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * User Repository
 *
 * Repository clas to wrap fetching users by email address.
 *
 * @category  Aligent
 * @package   LiveChatBundle
 * @author    Jim O'Halloran <jim@aligent.com.au>
 * @copyright 2017 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 **/
class UserRepository {

    /** @var UserManager  */
    protected $userManager;

    public function __construct(UserManager $userManager) {
        $this->userManager = $userManager;
    }


    /**
     * Fetch all of the matching user accounts for the agents involved in the
     * chat.
     *
     * @param $agents array Array of agent email addresses
     * @return array Array of user objects
     */
    public function getUsersForAgents($agents) {
        if (count($agents) == 0) {
            $users = [];
        } else {
            /** @var QueryBuilder $qb */
            $qb = $this->userManager->getRepository()->createQueryBuilder('u');

            $criteria     = new Criteria();
            $criteria->where(Criteria::expr()->in('email', $agents));
            $qb->addCriteria($criteria);

            $users = $qb->getQuery()->getResult();
        }

        if (count($users) == 0) {
            $qb = $this->userManager->getRepository()->createQueryBuilder('u');
            $qb->setMaxResults(1);
            $users = $qb->getQuery()->getResult();
        }

        return $users;
    }

}