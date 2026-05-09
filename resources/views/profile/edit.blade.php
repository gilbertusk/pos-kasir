<x-app-layout>
    @section('title', 'Profil Saya')
    @section('subtitle', 'Kelola informasi akun dan keamanan Anda')

    <div class="space-y-8">
        <div class="card p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card p-8 border-rose-100 bg-rose-50/10">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>

