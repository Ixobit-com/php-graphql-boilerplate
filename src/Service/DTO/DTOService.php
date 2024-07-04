<?php

namespace App\Service\DTO;

use App\DTO\BaseDTO;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOService
{
    public function __construct(
        private ValidatorInterface $validator)
    {}

    public function convertToDTO(ResolveInfo $info): array
    {
        $dto = [];
        foreach ($info->fieldDefinition->args as $argument) {
            $className = $argument->getType()->name;
            $dto[$className] = $this->convertToDTOObject(
                dto_class_name: $className,
                data: $info->variableValues[$argument->name]
            );
        }
        return $dto;
    }

    private function convertToDTOObject(string $dto_class_name, array $data): BaseDTO
    {
        $name = 'App\DTO\\' . $dto_class_name;
        $dto = new $name;

        foreach ($data as $name => $value) {
            $dto->$name = $value;
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationFailedException((string) $errors, $errors);
        }
        return $dto;
    }
}