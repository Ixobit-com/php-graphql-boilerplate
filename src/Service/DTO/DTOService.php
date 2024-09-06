<?php

declare(strict_types=1);

namespace App\Service\DTO;

use App\Entity\GraphQL\DTO\Common\BaseDTO;
use Doctrine\ORM\Mapping\MappingException;

/**
 * DTO Manipulations
 *  - hydrate Doctrine object from incoming DTO.
 *
 * Important: DTO property name must be equal as Doctrine Entity property name
 *
 * @example
 *  Right way: userUpdateInputDTO::profile -> \App\Entity\User::profile
 *  Wrong way: userUpdateInputDTO::user_profile -> \App\Entity\User::profile
 */
class DTOService
{
    private static array $graphQLScalarTypes = [
        'Int',      // A signed 32‐bit integer.
        'Float',    // A signed double-precision floating-point value.
        'String',   // A UTF‐8 character sequence.
        'Boolean',  // true or false.
        'ID',       // The ID scalar type represents a unique identifier, often used to refetch an object or as the key for a cache.
    ];

    public function __construct()
    {
    }

    /**
     * Hydration Entity from DTO object
     * Map describe how DTO fields move to Entity
     * Map format:
     * [ '<DTO field name>'    => ['property' => '<Entity field name>', 'map' => <map>], ... ].
     *
     * Example:
     * [
     *  'login'    => ['property' => 'Login'],
     *  'roles'    => ['property' => 'Roles'],
     *  'profile'  => ['property' => 'Profile',
     *      'map' => [
     *          'first_name' => ['property' => 'FirstName'],
     *          'last_name'  => ['property' => 'LastName'],
     *          'email'      => ['property' => 'Email'],
     *      ],
     *  ]
     * ]
     *
     * @throws MappingException
     * @throws \ReflectionException
     */
    public function hydrateEntityFromDTO(BaseDTO $dto, object $entity, ?array $map = null): object
    {
        $DTOReflection     = new \ReflectionClass($dto::class);
        $EntityReflection  = new \ReflectionClass($entity);

        foreach ($DTOReflection->getProperties() as $DTOproperty) {
            $dto_property_name = $DTOproperty->getName();

            if (array_key_exists($dto_property_name, $map)) {
                if (isset($dto->$dto_property_name)) { // DTO Object have value for this property (if empty - entity property will not change)
                    $setter_name = 'set'.$this->getAccessMethodName($dto_property_name, $map);
                    $getter_name = 'get'.$this->getAccessMethodName($dto_property_name, $map);

                    // Check getter, setter exists
                    if (!method_exists($entity, $setter_name) or !method_exists($entity, $getter_name)) {
                        throw new \LogicException("Entity '".$entity::class."' has not method '$setter_name' or '$getter_name'");
                    }

                    $value_from_dto = $dto->$dto_property_name; // get incoming property value

                    if ($DTOproperty->getType()->isBuiltin()) { // If DTO Property is a built-in type (any type that is not a class, interface, or trait), set entity property by setter
                        $entity->$setter_name($value_from_dto);
                    } else { // If DTO Property is another Type (i.e. custom type class) - try to set it by recursion
                        $entity_property_value = $entity->$getter_name();

                        if (!$value_from_dto instanceof BaseDTO) { // DTO property must extend BaseDTO class
                            throw new \LogicException("Unexpected entity class '".$entity_property_value::class."'; ".get_class($value_from_dto).' received');
                        }

                        $entity_property_type = $EntityReflection->getProperty($dto_property_name)->getType()->getName();
                        if (!class_exists($entity_property_type)) {
                            throw new \LogicException("Entity property $entity::$dto_property_name' try to set as undefined class '$entity_property_type'");
                        }

                        if (is_null($entity_property_value)) {
                            $entity_property_value = new $entity_property_type(); // Create empty Entity for property, if not exists (i.e. for new Entity)
                        } elseif (!$entity_property_value instanceof $entity_property_type) {
                            throw new \LogicException("Entity property '$dto_property_name' return '".gettype($entity_property_value)."'; ".$entity_property_type.' expected');
                        }

                        if (!isset($map[$dto_property_name]['map'])) {
                            throw new MappingException("DTO Mapping Exception: map for '$dto_property_name' is not exists");
                        }
                        $entity->$setter_name(
                            $this->hydrateEntityFromDTO($value_from_dto, $entity_property_value, $map[$dto_property_name]['map'])
                        );
                    }
                }
            } // DTO property has not mapping
        }

        return $entity;
    }

    /**
     * Get accessor name from map array.
     */
    private function getAccessMethodName(string $name, array $map): string
    {
        return ucfirst($map[$name]['property']);
    }
}
