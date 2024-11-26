<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use App\Models\User;
use Livewire\Attributes\Rule;
use App\Models\Country;
use Livewire\WithFileUploads;
use App\Models\Language;

new class extends Component {
    use Toast, WithFileUploads;
    public User $user;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email;

    #[Rule('sometimes')]
    public ?int $country_id=null;

     #[Rule('nullable|image|max:1024')]
     public $photo;

     #[Rule('required')]
    public array $my_languages = [];

    public function with(): array
    {
        return [
            'countries' => Country::All(),
            'languages' => Language::all(),
        ];
    }

    public function mount(): void
    {
        $this->fill($this->user);

        // Fill the selected languages property
        $this->my_languages = $this->user->languages->pluck('id')->all();
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->user->update($data);

        // Sync selection
        $this->user->languages()->sync($this->my_languages);

        // Upload file and save the avatar `url` on User model
        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }
        $this->success('User Successfully Updated', redirectTo: '/users');
    }
}; ?>

<div>
<x-header title="Update {{$user->name}}" separator />
<div class="grid gap-5 lg:grid-cols-2">
    <div>
        <x-form wire:submit="save">
            <x-file label="Avatar" wire:model='photo' accept='image/png, image/jpg, image/jpeg' crop-after-change>
                <img src="{{ $user->avatar?? '/empty-user.jpg'}}" alt="" class="h-40 rounded-lg">
            </x-file>
            <x-input label="Name" wire:model="name" />
            <x-input label="Email" wire:model="email" />
            <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---" />
            <x-choices-offline
                label="My languages"
                wire:model="my_languages"
                :options="$languages"
                searchable />

            <x-slot:actions>
                <x-button label="Cancel" link="/users" />
                <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </div>
    <div>
        {{-- Get a nice picture from `StorySet` web site --}}
        <img src="/Status update-cuate.png" width="350" class="mx-auto" />
    </div>
</div>
</div>
