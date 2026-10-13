<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, User $record): void {
                    if ($record->id === Auth::id()) {
                        $action->cancel();
                        throw ValidationException::withMessages([
                            'record' => 'You cannot delete your own account from here.',
                        ]);
                    }

                    if (UserResource::isLastActiveAdmin($record)) {
                        $action->cancel();
                        throw ValidationException::withMessages([
                            'record' => 'Cannot delete the last active admin.',
                        ]);
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Self-protection: disable role and is_active when editing yourself
        if ($record->id === Auth::id()) {
            $this->data['role'] = $data['role'];
            $this->data['is_active'] = $data['is_active'];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $original = $record->getOriginal();

        // Last-admin protection: prevent deactivating or demoting the last active admin
        if (UserResource::isLastActiveAdmin($record)) {
            if (isset($data['is_active']) && $data['is_active'] != $original['is_active']) {
                $data['is_active'] = $original['is_active'];
                $this->addError('data.is_active', 'Cannot deactivate the last active admin.');
            }

            if (isset($data['role']) && $data['role'] !== $original['role']) {
                $data['role'] = $original['role'];
                $this->addError('data.role', 'Cannot change the role of the last active admin.');
            }
        }

        // Self-protection: prevent admin from modifying their own role/active status
        // Only applies if not already handled by last-admin protection above
        if ($record->id === Auth::id()) {
            if (! UserResource::isLastActiveAdmin($record)) {
                $data['role'] = $original['role'];
                $data['is_active'] = $original['is_active'];
            }
        }

        return $data;
    }
}
