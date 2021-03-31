<?php

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Requests\UpdateAvatarRequest;
use App\Http\Api\V1\Transformers\ChildTransformer;
use App\Http\Api\V1\Requests\Child\CreateChildRequest;
use App\Http\Api\V1\Requests\Child\UpdateChildRequest;
use App\Http\Api\V1\Requests\UpdateRelationshipRequest;
use App\Models\Child\Child;
use App\Models\Tracker\TrackerType;
use App\Models\Gender;
use App\Models\Relationship\RelationshipRole;
use App\Services\AvatarService;
use App\Services\Child\ChildService;
use App\Services\Child\Dto\SaveChildDto;
use App\Services\User\Dto\AddChildDto;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ChildController
 *
 * @package App\Http\Api\V1\Controllers
 */
class ChildController extends Controller
{
    public function store(
        CreateChildRequest $request,
        ChildService $childService,
        UserService $userService
    ): Response {
        $saveChildDto = new SaveChildDto(
            name: $request->input('name'),
            gender: new Gender($request->input('gender')),
            birthDate: new Carbon($request->input('birth_date')),
            hairstyle: $request->input('hairstyle'),
            clothes: $request->input('clothes'),
            hairColor: $request->input('hair_color'),
            skinColor: $request->input('skin_color'),
        );
        $child = $childService->store($saveChildDto);

        $addChildDto = new AddChildDto(
            child: $child,
            role: new RelationshipRole($request->input('relationship.role')),
            label: $request->input('relationship.label'),
            primary: true,
        );
        $userService->addChild($this->getAuthUser(), $addChildDto);

        return $this->item($child, ChildTransformer::class);
    }

    public function show(int $id): Response
    {
        return $this->item($this->findChild($id), ChildTransformer::class);
    }

    public function update(UpdateChildRequest $request, int $id, ChildService $childService): Response
    {
        $child = $this->findChild($id);
        $saveChildDto = new SaveChildDto(
            name: $request->input('name'),
            gender: new Gender($request->input('gender')),
            birthDate: new Carbon($request->input('birth_date')),
            hairstyle: $request->input('hairstyle'),
            clothes: $request->input('clothes'),
            hairColor: $request->input('hair_color'),
            skinColor: $request->input('skin_color'),
        );
        $child = $childService->update($child, $saveChildDto);

        return $this->item($child, ChildTransformer::class);
    }

    public function destroy(int $id): Response
    {
        $child = $this->findChild($id);
        $this->authorize('destroy', $child);
        $child->delete();

        return $this->empty();
    }

    public function updateRelationship(
        UpdateRelationshipRequest $request,
        int $id,
        ChildService $childService
    ): Response {
        $child = $this->findChild($id);
        $this->authorize('updateRelationship', $child);
        $childService->changePrimaryRight(
            $child,
            $request->input('user_id'),
            $request->input('primary')
        );

        return $this->item($child, ChildTransformer::class);
    }

    public function destroyRelationship(int $id, Request $request): Response
    {
        $this->validate($request, [
            'user_id' => 'required|int',
        ]);

        $child = $this->findChild($id);
        $this->authorize('destroyRelationship', $child);
        $child->users()->detach($request->input('user_id'));

        return $this->empty();
    }

    public function updateAvatar(int $id, UpdateAvatarRequest $request, AvatarService $service): Response
    {
        $child = $this->findChild($id);
        $service->updateChildAvatar($child, $request->file('file'));

        return $this->response([
            'url' => $child->avatar_url,
        ]);
    }

    public function toggleTracker(int $id, string $trackerType): Response
    {
        $child = $this->findChild($id);
        $child->toggleTracker(TrackerType::make($trackerType));

        return $this->empty();
    }

    private function findChild(int $id): Child
    {
        /* @var Child $child */
        $child = $this->getAuthUser()
            ->children()
            ->find($id);
        if ($child === null) {
            throw new NotFoundHttpException('Child not found.');
        }

        return $child;
    }
}
