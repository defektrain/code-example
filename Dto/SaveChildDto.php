<?php

namespace App\Services\Child\Dto;

use App\Models\Gender;
use Carbon\Carbon;

/**
 * Class SaveChildDto
 *
 * @package App\Services\Child\Dto
 */
class SaveChildDto
{
    public function __construct(
        public string $name,
        public Gender $gender,
        public Carbon $birthDate,
        public ?string $avatarUrl = null,
        public ?string $hairstyle = null,
        public ?string $clothes = null,
        public ?string $hairColor = null,
        public ?string $skinColor = null,
    ) {
    }
}
