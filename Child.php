<?php

namespace App\Models\Child;

use App\Models\Gender;
use App\Models\Relationship\Relationship;
use App\Models\Relationship\RelationshipInvite;
use App\Models\Task\ChildTask;
use App\Models\Task\Task;
use App\Models\Tracker\DurationLog;
use App\Models\Tracker\FeedingLog;
use App\Models\Tracker\MeasurementLog;
use App\Models\Tracker\TrackerType;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Child
 *
 * @property int                       $id
 * @property string                    $name
 * @property Gender                    $gender
 * @property Carbon                    $birth_date
 * @property string|null               $avatar_url
 * @property string|null               $hairstyle
 * @property string|null               $clothes
 * @property string|null               $hair_color
 * @property string|null               $skin_color
 * @property Carbon                    $created_at
 * @property Carbon                    $updated_at
 * @property Teeth|null                $teeth
 * @property string[]                  $enabled_trackers
 *
 * @property-read User[]               $users
 * @property-read Relationship[]       $relationships
 * @property-read RelationshipInvite[] $relationshipInvites
 * @property-read DurationLog[]        $durationLogs
 * @property-read MeasurementLog[]     $measurementLogs
 * @property-read FeedingLog[]         $feedingLogs
 *
 * @package App\Models\Child
 */
class Child extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $table = 'children';

    /** @inheritdoc */
    protected $casts = [
        'birth_date' => 'date',
        'teeth' => Teeth::class,
        'enabled_trackers' => 'array',
    ];

    /** @inheritdoc */
    protected $fillable = [
        'name',
        'gender',
        'birth_date',
        'hairstyle',
        'clothes',
        'hair_color',
        'skin_color',
        'teeth',
    ];

    /** @inheritdoc */
    protected static function booted()
    {
        static::creating(function (self $invite) {
            // Enable all trackers when creating a child
            $invite->enabled_trackers = TrackerType::toValues();
        });
    }

    public function toggleTracker(TrackerType $type): void
    {
        if (in_array($type, $this->enabled_trackers)) {
            $this->enabled_trackers = array_values(array_diff($this->enabled_trackers, [$type]));
        } else {
            $this->enabled_trackers = [...$this->enabled_trackers, $type];
        }

        $this->save();
    }

    public function getGenderAttribute(?string $value): ?Gender
    {
        return $value !== null ? Gender::make($value) : null;
    }

    public function setGenderAttribute(Gender $gender): void
    {
        $this->attributes['gender'] = $gender->value;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'relationships');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)
            ->using(ChildTask::class)
            ->withPivot('scheduled_for', 'completed_at');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class);
    }

    public function relationshipInvites(): HasMany
    {
        return $this->hasMany(RelationshipInvite::class);
    }

    public function userHasPrimaryRight(User $user): bool
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('primary', true)
            ->exists();
    }

    public function age(): int
    {
        return $this->birth_date->age;
    }

    public function durationLogs(): HasMany
    {
        return $this->hasMany(DurationLog::class)->orderBy('start_at', 'ASC');
    }

    public function measurementLogs(): HasMany
    {
        return $this->hasMany(MeasurementLog::class)->orderBy('measured_at', 'ASC');
    }

    public function feedingLogs(): HasMany
    {
        return $this->hasMany(FeedingLog::class)->orderBy('eat_at', 'ASC');
    }
}
