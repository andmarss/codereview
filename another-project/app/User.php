<?php

namespace App;

use App\Workflow\AuthWorkflow;
use Brexis\LaravelWorkflow\Traits\WorkflowTrait;
use Crabler\Common\Models\Traits\Parameterizable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Silvanite\Brandenburg\Traits\HasRoles;
use Symfony\Component\Workflow\Workflow;

/**
 * Class User
 * @package App
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property array $params
 * @property array $places
 * @property boolean $verified
 *
 * @property VerifyUser $verify_user
 * @property UserFile[] $files
 * @property Specialist $specialist
 */

class User extends Authenticatable
{
    use Notifiable, WorkflowTrait, Parameterizable, HasRoles;

    const ROLE_USER = 1;
    const ROLE_MODERATOR = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'params',
        'places',
        'verified',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'params' => 'array',
        'places' => 'array',
    ];

    /**
     * модератор или обычный пользователь
     * @return bool
     */
    public function isModerator(): bool
    {
        return (bool) !is_null($this->roles()->where('id', static::ROLE_MODERATOR)->first());
    }
    /**
     * @return HasOne
     */
    public function verify_user(): HasOne
    {
        return $this->hasOne(VerifyUser::class);
    }
    /**
     * @return HasOne@
     */
    public function specialist(): HasOne
    {
       return $this->hasOne(Specialist::class, 'user_id', 'id');
    }
    /**
     * @return bool
     */
    public function isVerified(): bool
    {
       return (bool) $this->verified;
    }

    /**
     * @return bool
     */
    public function isDoctor(): bool
    {
       return (bool) !is_null($this->specialist);
    }
    /**
     * @return array
     */
    public function getTransitionNames(): array
    {
       $transitions = [];
       foreach ($this->getWorkflow(AuthWorkflow::WORKFLOW_NAME)->getEnabledTransitions($this) as $transition) {
           $transitions[] = $transition->getName();
       }

       return $transitions;
    }

    /**
     * @param string $name
     * @return Workflow
     */
    public function getWorkflow(string $name): Workflow
    {
        return \Workflow::get($this, $name);
    }

    /**
     * @return HasMany
     */
    public function files(): HasMany
    {
       return $this->hasMany(UserFile::class, 'user_id', 'id');
    }

    /**
     * Зарегестрирован ли пользователь
     * @return bool
     */
    public function isRegistered(): bool
    {
        /**
         * @var array $transitions
         */
        $transitions = $this->getTransitionNames();
        /**
         * Если пользователь уже подтвердил регистрацию
         * Или его регистрацию подтвердил модератор - он зарегестрирован
         * @var bool $confirmed
         */
        $confirmed = $this->isConfirmed()
            || $this->isModeratorConfirmed()
            || $this->isDataChanged()
            || $this->isDataChangeConfirmed()
            || $this->isWantsToBeDeleted()
            || $this->isDeleted()
            || $this->isReturnedToRegister()
            || $this->isReturnedToDataChange();

       return (bool) $confirmed ? $confirmed : collect($transitions)->contains(AuthWorkflow::TRANSITION_AUTH_CONFIRM);
    }

    /**
     * Была ли подтверждена регистрация пользователем
     * @return bool
     */
    public function isConfirmed(): bool
    {
        /**
         * @var array $transitions
         */
        $transitions = $this->getTransitionNames();
        /**
         * @var array $allowed
         */
        $allowed = [
            AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM,
            AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
            AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM
        ];
        /**
         * @var bool $confirmed
         */
        $confirmed = $this->isModeratorConfirmed()
            || $this->isDataChanged()
            || $this->isDataChangeConfirmed()
            || $this->isWantsToBeDeleted()
            || $this->isDeleted()
            || $this->isReturnedToDataChange();

        return (bool) $confirmed ? $confirmed : collect($transitions)
                ->filter(function (string $transition) use ($allowed) {
                    return in_array($transition, $allowed);
                })->count() > 0;
    }
    /**
     * Была ли подтверждена регистрация пользователем
     * @return bool
     */
    public function isModeratorConfirmed(): bool
    {
        /**
         * @var array $transitions
         */
        $transitions = $this->getTransitionNames();
        /**
         * @var array $allowed
         */
        $allowed = [
            AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE,
            AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM,
            AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE
        ];
        /**
         * @var bool $confirmed
         */
        $confirmed = $this->isDataChanged()
            || $this->isDataChangeConfirmed()
            || $this->isWantsToBeDeleted()
            || $this->isDeleted()
            || $this->isReturnedToDataChange();

        return (bool) $confirmed ? $confirmed : collect($transitions)
                ->filter(function (string $transition) use ($allowed) {
                    return in_array($transition, $allowed);
                })->count() > 0;
    }

    /**
     * Пользователь внес изменения на своем профиле (специалиста)?
     * @return bool
     */
    public function isDataChanged(): bool
    {
        return (bool) isset($this->places[AuthWorkflow::PLACE_AUTH_CHANGED]);
    }

    /**
     * Модератор подтвердил изменения в профиле?
     * @return bool
     */
    public function isDataChangeConfirmed(): bool
    {
        return (bool) isset($this->places[AuthWorkflow::PLACE_AUTH_CHANGE_CONFIRMED]);
    }

    /**
     * Пользователь может удалить свой аккаунт?
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        // если пользователь - не доктор
        // то удалить свой профиль он не может
        if(!$this->isDoctor()) return false;
        /**
         * @var array $transitions
         */
        $transitions = $this->getTransitionNames();

        return (bool) collect($transitions)->contains(AuthWorkflow::TRANSITION_DELETE) && !$this->wantsToBeRestore();
    }

    /**
     * Профиль пользователя удален?
     * @return bool
     */
    public function isDeleted(): bool
    {
        // если пользователь - не доктор
        // то удалить свой профиль он не может
        if(!$this->isDoctor()) return false;

        return isset( $this->places[AuthWorkflow::PLACE_DELETE_ACCOUNT] ) && !$this->wantsToBeRestore();
    }
    /**
     * Пользователь подавал заявку на удаление профиля?
     * @return bool
     */
    public function isWantsToBeDeleted(): bool
    {
        // если пользователь - не доктор
        // то удалить свой профиль он не может
        if(!$this->isDoctor()) return false;

        return (bool) $this->specialist->is_deletion_requested;
    }

    /**
     * Пользователя вернули к шагу регистрации?
     * @return bool
     */
    public function isReturnedToRegister(): bool
    {
        return isset($this->places[AuthWorkflow::PLACE_AUTH_REGISTER_RETURNED]);
    }

    /**
     * Пользователя вернули к изменению данных?
     * @return bool
     */
    public function isReturnedToDataChange(): bool
    {
        return isset($this->places[AuthWorkflow::PLACE_AUTH_CHANGE_RETURNED]);
    }
    /**
     * Возвращает массив, в котором указано, что пользователь уже изменил
     * Необходим для доступа к модерации
     * @param array|string $param
     * @param string|bool $value
     * @return mixed
     */
    public function changed($param = null, $value = null)
    {
        if(!$this->isDoctor()) return [];

        if (is_null($this->getParam('changed'))) {
           $this->resetChanges();
        }

        if(is_array($param) && count($param) > 0) {
            $this->setParam('changed', $param);

            $this->save();

            return $this;
        }

        if(is_null($param) && is_null($value)) {
           return $this->getParam('changed');
        }

        if(!is_null($param) && is_null($value)) {
           return $this->getParam(sprintf("changed.%s", $param));
        }

        if(!is_null($param) && !is_null($value)) {
           $this->setParam(sprintf("changed.%s", $param), $value);

           $this->save();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function resetChanges(): bool
    {
       try {
           $this->setParam('changed', [
               'firstname' => false,
               'secondname' => false,
               'lastname' => false,
               'description' => false,
               'image' => false
           ]);

           $this->save();

           return true;
       } catch (\Exception $exception) {
           return false;
       }
    }

    /**
     * Пользователь внес все изменения?
     * @return bool
     */
    public function allChanged(): bool
    {
        if(!$this->isDoctor()) return false;

        return collect($this->changed())
            ->map(function (bool $changed, string $prop): bool  {
                if(!is_null($this->specialist->{$prop}) && !$changed) {
                    $this->changed($prop, true);

                    return true;
                }

                return $changed;
            })->every(function (bool $changed): bool {
                return $changed;
            });
    }

    /**
     * @param string $transition
     * @return bool
     */
    public function authWorkflowCan(string $transition): bool
    {
       return $this->workflow_can($transition, AuthWorkflow::WORKFLOW_NAME);
    }

    /**
     * @param string $transition
     * @return void
     */
    public function authWorkflowApply(string $transition): void
    {
        $this->workflow_apply($transition, AuthWorkflow::WORKFLOW_NAME);
    }
    /**
     * @return string
     */
    public function getCause(): string
    {
       if (is_null($this->getParam('cause'))) {
           $this->setParam('cause', '');

           $this->save();
       }

       return $this->getParam('cause');
    }
    /**
     * @param string $cause
     */
    public function setCause(string $cause)
    {
        $this->setParam('cause', $cause);

        $this->save();
    }

    /**
     * Пользователь хочет восстановить профиль
     * @return bool
     */
    public function wantsToBeRestore(): bool
    {
        if(is_null($this->getParam('restore'))) {
            $this->setParam('restore', false);

            $this->save();
        }

        return $this->getParam('restore');
    }

    /**
     * @param bool $restore
     */
    public function restore(bool $restore)
    {
       $this->setParam('restore', $restore);
    }
    /**
     * Получить пользователей НЕ врачей
     * @return Builder
     */
    public static function notDoctors(): Builder
    {
        return static::whereHas('roles', function(Builder $query) {
            $query->where('id', static::ROLE_USER);
        })->whereDoesntHave('specialist');
    }
    /**
     * Получить пользователей врачей
     * @return Builder
     */
    public static function doctors(): Builder
    {
        return static::whereHas('roles', function(Builder $query) {
            $query->where('id', static::ROLE_USER);
        })->whereHas('specialist');
    }

    /**
     * Получить врачей, которые изменили свои данные
     * Или подали заявление на удаление профиля
     * @return Builder
     */
    public static function changedOrDeletedDoctors(): Builder
    {
        return static::doctors()
            ->whereNotNull('places->changed');
    }
}
