<?php

namespace Aedart\Acl\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;

/**
 * Has Roles
 *
 * @property \Aedart\Acl\Models\Role[]|Collection $roles The roles assigned to this model
 *
 * @author Alin Eugen Deac <ade@rspsystems.com>
 * @package Aedart\Acl\Traits
 */
trait HasRoles
{
    use AclComponentsTrait;

    /*****************************************************************
     * Operations
     ****************************************************************/

    /**
     * Determine if model is assigned given role
     *
     * If an array of roles is given, then method will return true, if
     * any of the given roles are assigned.
     *
     * @see hasAnyRole
     *
     * @param string|int|\Aedart\Acl\Models\Role|Collection|string[]|int[]|\Aedart\Acl\Models\Role[] $roles Slugs, ids or Role instances or list of roles
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasRole($roles): bool
    {
        /** @var Model|\Aedart\Acl\Models\Role $roleModel */
        $roleModel = $this->aclRoleModelInstance();

        // When a role's id is given
        if (is_numeric($roles)) {
            return $this->roles->contains($roleModel->getKeyName(), $roles);
        }

        // When a role's slug is given
        if (is_string($roles)) {
            return $this->roles->contains($roleModel->getSlugKeyName(), $roles);
        }

        // When a role instance is given
        $roleClass = $this->aclRoleModel();
        if ($roles instanceof $roleClass) {
            return $this->roles->contains($roleModel->getKeyName(), $roles->id);
        }

        // When a collection of roles is given
        if ($roles instanceof Collection) {
            return $roles->intersect($this->roles)->isNotEmpty();
        }

        // When an array of roles is given
        if (is_array($roles)) {
            return $this->hasAnyRole($roles);
        }

        // Unable to determine how to check given role, thus we must fail...
        throw new InvalidArgumentException(sprintf(
            'Unable to determine is given role(s) are assigned. Accepted values are slugs, ids, role instance or array. %s given',
            gettype($roles)
        ));
    }

    /**
     * Determine if model is assigned any of the given roles
     *
     * @param string[]|int[]|\Aedart\Acl\Models\Role[] $roles Slugs, ids or Role instances
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if model is assigned all of the given roles
     *
     * Method will return false is any of given roles are not assigned
     * to this model.
     *
     * @param string[]|int[]|\Aedart\Acl\Models\Role[] $roles Slugs, ids or Role instances
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /*****************************************************************
     * Relations
     ****************************************************************/

    /**
     * The roles that are assigned to this model
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $role = $this->aclRoleModelInstance();

        return $this->belongsToMany(
            $this->aclRoleModel(),
            $this->aclTable('users_roles'),
            $this->getForeignKey(),
            $role->getForeignKey()
        );
    }
}