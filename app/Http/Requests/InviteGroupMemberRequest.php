<?php

namespace App\Http\Requests;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InviteGroupMemberRequest extends FormRequest
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
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $group = $this->route('group');
                $ids = collect($this->input('user_ids', []))->unique();

                foreach ($ids as $userId) {
                    if ((int) $userId === (int) $this->user()->id) {
                        $validator->errors()->add('user_ids', __('econature.groups.cannot_invite_self'));
                    }

                    if ($group->memberships()->where('user_id', $userId)->where('status', 'approved')->exists()) {
                        $validator->errors()->add('user_ids', __('econature.groups.already_member'));
                    }
                }
            },
        ];
    }
}
