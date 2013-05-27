<?php

namespace SpiffyAuthorize\Provider\Permission\ObjectRepository;

use Doctrine\Common\Persistence\ObjectRepository;
use SpiffyAuthorize\AuthorizeEvent;
use SpiffyAuthorize\Permission\PermissionInterface;
use SpiffyAuthorize\Provider\AbstractProvider;
use SpiffyAuthorize\Provider\Permission;
use SpiffyAuthorize\Provider\Role;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;

class RbacProvider extends AbstractProvider implements Permission\ProviderInterface
{
    use ListenerAggregateTrait;
    use Role\ExtractorTrait;

    /**
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * @param ObjectRepository $objectRepository
     * @return RbacProvider
     */
    public function setObjectRepository(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
        return $this;
    }

    /**
     * @return ObjectRepository
     */
    public function getObjectRepository()
    {
        return $this->objectRepository;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(AuthorizeEvent::EVENT_INIT, [$this, 'load']);
    }

    /**
     * @param AuthorizeEvent $e
     * @throws Permission\Exception\InvalidArgumentException if permissions are an array with invalid format
     */
    public function load(AuthorizeEvent $e)
    {
        /** @var \Zend\Permissions\Rbac\Rbac $rbac */
        $rbac   = $e->getTarget();
        $result = $this->getObjectRepository()->findAll();

        foreach ($result as $entity) {
            $permission = null;
            $roles      = [];

            if ($entity instanceof PermissionInterface) {
                $permission = $entity->getName();
                $roles      = $entity->getRoles();
            } else if (is_array($entity)) {
                $permission = key($entity);
                $roles      = current($entity);

                if (is_numeric($permission)) {
                    throw new Permission\Exception\InvalidArgumentException(
                        'roles provided with no permission name'
                    );
                }

                if (!is_array($roles)) {
                    $roles = [ $roles ];
                }
            } else {
                throw new Permission\Exception\InvalidArgumentException('unknown permission entity type');
            }

            foreach ($roles as $role) {
                $roleName = $this->extractRole($role);

                if ($roleName) {
                    $rbac->getRole($roleName)
                         ->addPermission($permission);
                }
            }
        }
    }
}