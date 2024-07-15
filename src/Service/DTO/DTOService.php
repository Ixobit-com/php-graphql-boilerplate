<?php

namespace App\Service\DTO;

use App\GraphQL\DTO\BaseDTO;
use GraphQL\Type\Definition\ResolveInfo;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOService
{
    static array $graphQLScalarTypes = [
        "Int",      // A signed 32‐bit integer.
        "Float",    // A signed double-precision floating-point value.
        "String",   // A UTF‐8 character sequence.
        "Boolean",  // true or false.
        "ID",       // The ID scalar type represents a unique identifier, often used to refetch an object or as the key for a cache.
    ];

    public function __construct(
        private ValidatorInterface $validator
    )
    {}

    /**
     * Convert GraphQL arguments array into PHP array with scalar values and DTO objects
     * @param ResolveInfo $info
     * @return array
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function convertGraphQLToDTO(ResolveInfo $info): array
    {
        $dto = []; $fieldNodesId = 0;

        // $info->fieldNodes may contain more than 1 element!!! May be in case with several queries in one time
        if (count($info->fieldNodes) != 1) {
            throw new \InvalidArgumentException('FieldNodes contain more than 1 element');
        }

        foreach ($info->fieldDefinition->args as $argument) {
            $variableName = $info->fieldNodes[0]->arguments[$fieldNodesId]->value->name->value;
            $className = $this->prepareDTOClassName($argument->getType()->toString() ?? '');
            if (in_array($className, self::$graphQLScalarTypes)) {

                // try assign non scalar value to scalar declared variable
                if (!is_scalar($info->variableValues[$variableName])) {
                    throw new \InvalidArgumentException("Variable $className declared as scalar but it is a '".gettype($info->variableValues[$variableName])."'");
                }

                $dto[$variableName] = $info->variableValues[$variableName];
            } else {
                $dto[$variableName] = $this->createDTOObject(
                    dto_class_name: 'App\DTO\\' . $className,
                    data: $info->variableValues[$variableName]
                );
            }
            $fieldNodesId++;
        }
        return $dto;
    }

    /**
     * @param string $dto_class_name
     * @param array $data
     * @return BaseDTO
     * @throws \ReflectionException
     * @throws ValidationFailedException
     */
    private function createDTOObject(string $dto_class_name, array $data): BaseDTO
    {
        $dto = new $dto_class_name;
        $refDTO = new \ReflectionClass($dto::class);

        foreach ($data as $property_name => $value) {
            if ($refDTO->getProperty($property_name)->getType()->isBuiltin()) {
                $dto->$property_name = $value;
            } else {
                $dto->$property_name = $this->createDTOObject($refDTO->getProperty($property_name)->getType()->getName(), $value);
            }
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationFailedException((string) $errors, $errors);
        }
        return $dto;
    }

    /**
     * Use for convert DTO type name to PHP DTO Class name (i.e. ID! -> ID)
     * @param string $className
     * @return string
     */
    private function prepareDTOClassName(string $className): string
    {
        return trim($className, '! ');
    }

    /**
     * Hydrate Entity object from DTO Object
     *  - DTO properties must be named exactly as Entity properties
     * @example
     * DTO property: public profileCreateInputDTO $profile;
     * Entity property: private ?Profile $profile = null;
     *
     * @param BaseDTO $dto
     * @param object $entity
     * @return object
     * @throws \ReflectionException
     */
    public function hydrateEntityFromDTO(BaseDTO $dto, object $entity): object
    {

        $refDTO     = new \ReflectionClass($dto::class);
        $refEntity  = new \ReflectionClass($entity);

        foreach ($refDTO->getProperties() as $property) {
            $property_name = $property->getName();
            if (isset($dto->$property_name)) { // DTO Object have value for this property (if empty - property will not change)
                $normalized_property_name = $this->nameNormalize($property_name);

                // Getters\Setters to Doctrine style: setPropertyName, getPropertyName
                $setter_name = 'set' . $normalized_property_name;
                $getter_name = 'get' . $normalized_property_name;

                // Check getter, setter exists
                if (!method_exists($entity, $setter_name) or !method_exists($entity, $getter_name)) {
                    throw new \LogicException("Entity '".$entity::class."' has not method '$setter_name' or '$getter_name'");
                }

                $value = $dto->$property_name; // get incoming property value

                if ($property->getType()->isBuiltin()) { // If DTO Property is scalar - set entity property by setter
                    $entity->$setter_name($value);
                } else { // If DTO Property is another Type (i.e. custom type) - try to set it by recursion
                    $entity_from_object = $entity->$getter_name();

                    if (!$value instanceof BaseDTO) {
                        throw new \LogicException("Unexpected entity class '" . $entity_from_object::class . "'; '$normalized_property_name' expected");
                    }

                    $entity_property_type = $refEntity->getProperty($property_name)->getType()->getName();
                    if (!class_exists($entity_property_type)) {
                        throw new \LogicException("Entity property '$property_name' try to update as undefined class '$entity_property_type'");
                    }

                    if (is_null($entity_from_object)) {
                        $entity_from_object = new $entity_property_type; // Create new Entity for property, if not exists (i.e. for new Entity)
                    } elseif (is_object($entity_from_object)) {
                        if (!$entity_from_object instanceof $entity_property_type) {
                            throw new \LogicException("Entity property '$property_name' return '".gettype($entity_from_object)."'; ".$entity_property_type." expected");
                        }
                    }

                    $entity->$setter_name($this->hydrateEntityFromDTO($value, $entity_from_object));
                }
            }
        }
        return $entity;
    }

    /**
     * Normalize property name
     *  - from underscore to CamelCase
     *  - First letter to uppercase
     *
     * @param string $name
     * @return string
     */
    private function nameNormalize(string $name): string
    {
        $substrings = explode('_', $name);
        array_walk($substrings, function (&$subname) {
            $subname = ucfirst(strtolower($subname));
            }
        );
        return implode('', $substrings);
    }
}