<?php

namespace App\Services\Child;

use App\Models\Child\Child;
use App\Services\Child\Dto\SaveChildDto;

/**
 * Class ChildService
 *
 * @package App\Services\Child
 */
class ChildService
{
    public function store(SaveChildDto $dto): Child
    {
        return Child::create([
            'name' => $dto->name,
            'gender' => $dto->gender,
            'birth_date' => $dto->birthDate,
            'hairstyle' => $dto->hairstyle,
            'clothes' => $dto->clothes,
            'hair_color' => $dto->hairColor,
            'skin_color' => $dto->skinColor,
        ]);
    }

    public function update(Child $child, SaveChildDto $dto): Child
    {
        $child->update([
            'name' => $dto->name,
            'gender' => $dto->gender,
            'birth_date' => $dto->birthDate,
            'hairstyle' => $dto->hairstyle,
            'clothes' => $dto->clothes,
            'hair_color' => $dto->hairColor,
            'skin_color' => $dto->skinColor,
        ]);

        return $child;
    }

    public function changePrimaryRight(Child $child, int $userId, bool $primary): void
    {
        $child->users()->updateExistingPivot($userId, [
            'primary' => $primary,
        ]);
    }
}
