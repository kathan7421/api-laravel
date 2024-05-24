<?php
/**
 * Request trait
 *
 * @category RequestTrait
 * @author   Codal <developer@codal.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://local.legionmedia.com/
 */

namespace App\Traits;

/**
 * Request trait
 *
 * @category RequestTrait
 * @author   Codal <developer@codal.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://local.legionmedia.com/
 */
trait RequestTrait
{
    protected $authToken = null;

    /**
     * This will chek user is superadmin or not
     *
     * @return boolean
     */
    public function isSuperAdmin($instanceId) :bool
    {
        return (
            $this->currentUser->getTableName() === 'users'
            && $this->currentUser->type == 0
            && $this->currentUser->instance_id == $instanceId
        );
    }

    /**
     * This will chek user is superadmin or not
     *
     * @return boolean
     */
    public function isManager($instanceId) :bool
    {
        return (
            $this->currentUser->getTableName() === 'users'
            && $this->currentUser->type == 1
            && $this->currentUser->instance_id == $instanceId
        );
    }


    /**
     * This will chek user is superadmin or not
     *
     * @return boolean
     */
    public function isAgent($instanceId) :bool
    {
        return (
            $this->currentUser->getTableName() === 'users'
            && $this->currentUser->type == 2
            && $this->currentUser->instance_id == $instanceId
        );
    }

    /**
     * This will chek user is superadmin or not
     *
     * @return boolean
     */
    public function isAffiliate($instanceId) :bool
    {
        return (
            $this->currentUser->getTableName() === 'users'
            && $this->currentUser->type == 3
            && $this->currentUser->instance_id == $instanceId
        );
    }

    /**
     * This will check user is master admin or not
     *
     * @return boolean
     */
    public function isMasterAdmin() : bool
    {
        return $this->currentUser->getTableName() === 'admins';
    }
}
