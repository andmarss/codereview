<?php

namespace App\DataMapper;

use App\User;
use App\UserFile;
use Illuminate\Support\Collection;

class UserMapper extends Mapper
{
    public static function map(User $user): Collection
    {
        /**
         * @var UserFile[] $files
         */
        $files = $user->files;
        /**
         * @var User $autorized
         */
        $autorized = auth()->user();

        return collect([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'files' => $files,
            'filesLoaded' => $files->count(),
            'transitions' => $user->getTransitionNames(),
            'registered' => $user->isRegistered(),
            'confirmed' => $user->isConfirmed(),
            'moderator_confirmed' => $user->isModeratorConfirmed(),
            'data_changed' => $user->isDataChanged(),
            'change_confirmed' => $user->isDataChangeConfirmed(),
            'can_be_deleted' => $user->canBeDeleted(),
            'deleted' => $user->isDeleted(),
            'will_be_deleted' => $user->isWantsToBeDeleted(),
            'returned_to_register' => $user->isReturnedToRegister(),
            'returned_to_change' => $user->isReturnedToDataChange(),
            'cause' => $user->getCause(),
            'will_be_restored' => $user->wantsToBeRestore()
        ])
            ->merge(static::getSpecialistData($user))
            ->merge($autorized->isModerator() ? [
                'changes' => $user->changed()
            ] : []);
    }
    /**
     * @param User $user
     * @return array
     */
    protected static function getSpecialistData(User $user): array
    {
        return $user->isDoctor() ? [
            'specialist' => $user->specialist
        ] : [];
    }
}
