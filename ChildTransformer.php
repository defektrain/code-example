<?php

namespace App\Http\Api\V1\Transformers;

use App\Http\Services\Fractal\Transformer;
use App\Models\Child\Child;
use League\Fractal\Resource\Collection;

/**
 * Class ChildTransformer
 *
 * @package App\Http\Api\V1\Transformers
 */
class ChildTransformer extends Transformer
{
    /** @inheritdoc */
    protected $defaultIncludes = [
        'relationships',
        'relationship_invites',
    ];

    public function transform(Child $child): array
    {
        return [
            'id' => $child->id,
            'name' => $child->name,
            'gender' => $child->gender,
            'birth_date' => $child->birth_date->format('Y-m-d'),
            'avatar_url' => $child->avatar_url,
            'hairstyle' => $child->hairstyle,
            'clothes' => $child->clothes,
            'hair_color' => $child->hair_color,
            'skin_color' => $child->skin_color,
            'enabled_trackers' => $child->enabled_trackers,
        ];
    }

    public function includeRelationships(Child $child): Collection
    {
        return $this->collection($child->relationships, new RelationshipTransformer());
    }

    public function includeRelationshipInvites(Child $child): Collection
    {
        return $this->collection(
            $child->relationshipInvites()->unaccepted()->get(),
            new RelationshipInviteTransformer()
        );
    }
}
