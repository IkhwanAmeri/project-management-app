<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Project Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500   ">{{ old('description', $project->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500   ">
                @foreach (['planning', 'active', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $project->status ?? 'planning') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>

        <div>
            <x-input-label for="start_date" value="Start Date" />
            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', isset($project) && $project->start_date ? $project->start_date->format('Y-m-d') : '')" />
            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
        </div>

        <div>
            <x-input-label for="end_date" value="End Date" />
            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', isset($project) && $project->end_date ? $project->end_date->format('Y-m-d') : '')" />
            <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
        </div>
    </div>

    @isset($project)
        <div class="border-t border-gray-200 pt-6 ">
            <h3 class="font-medium text-gray-900 ">Current Members</h3>
            <ul class="mt-3 space-y-2 text-sm text-gray-600 ">
                @foreach ($project->members as $member)
                    <li>{{ $member->name }} <span class="text-gray-500">({{ $member->pivot->role }})</span></li>
                @endforeach
            </ul>
        </div>
    @endisset

    <div class="border-t border-gray-200 pt-6 " x-data="{ newMembers: [] }">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="font-medium text-gray-900 ">Add Members</h3>
                <p class="mt-1 text-sm text-gray-500">Choose users and their role in this project.</p>
            </div>
            <button type="button" @click="newMembers.push({ user_id: '', role: 'Member' })" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Add another</button>
        </div>
        <div class="mt-4 space-y-3">
            <template x-for="(member, index) in newMembers" :key="index">
                <div class="grid gap-3 sm:grid-cols-[1fr_10rem_auto]">
                    <select x-model="member.user_id" :name="`members[${index}][user_id]`" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500   ">
                        <option value="">Select user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <select x-model="member.role" :name="`members[${index}][role]`" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500   ">
                        <option value="Member">Member</option>
                        <option value="Manager">Manager</option>
                    </select>
                    <button type="button" x-show="newMembers.length > 1" @click="newMembers.splice(index, 1)" class="text-sm text-red-600 hover:text-red-500">Remove</button>
                </div>
            </template>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('members')" />
    </div>
</div>
