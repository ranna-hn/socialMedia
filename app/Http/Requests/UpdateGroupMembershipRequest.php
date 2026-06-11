<?php

namespace App\Http\Requests;

use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateGroupMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Group $group */
        $group = $this->route('group');

        return $group && $group->isAdmin($this->user());
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([GroupUser::ROLE_ADMIN, GroupUser::ROLE_MEMBER])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Group $group */
                $group = $this->route('group');
                /** @var GroupUser $membership */
                $membership = $this->route('membership');

                if ($membership->group_id !== $group->id) {
                    $validator->errors()->add('membership', __('econature.groups.invalid_membership'));
                }

                if ($membership->user_id === $group->user_id && $this->input('role') !== GroupUser::ROLE_ADMIN) {
                    $validator->errors()->add('role', __('econature.groups.owner_must_remain_admin'));
                }
            },
        ];
    }
}
