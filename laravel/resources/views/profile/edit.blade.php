<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500 font-medium">Paramètres</p>
            <h1 style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:700;color:#1e293b;">
                Mon Profil 👤
            </h1>
        </div>
    </x-slot>

    <div style="max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:1.75rem;">

        <!-- Profile Card -->
        <div class="glass-card" style="padding:2rem;">
            <!-- Avatar + Info header -->
            <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border);">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4f46e5&color=fff&size=80"
                     style="width:72px;height:72px;border-radius:50%;border:3px solid white;box-shadow:0 4px 12px rgba(79,70,229,0.25);"
                     alt="Avatar">
                <div>
                    <h2 style="font-weight:700;font-size:1.15rem;color:#1e293b;">{{ Auth::user()->name }}</h2>
                    <p style="color:#64748b;font-size:0.875rem;">{{ Auth::user()->email }}</p>
                    <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                        <span class="badge {{ Auth::user()->role === 'admin' ? 'badge-danger' : 'badge-info' }}">
                            <i class="fas {{ Auth::user()->role === 'admin' ? 'fa-shield-halved' : 'fa-user' }} mr-1"></i>
                            {{ Auth::user()->role === 'admin' ? 'Admin Global' : 'Utilisateur' }}
                        </span>
                        <span class="badge {{ Auth::user()->is_banned ? 'badge-danger' : 'badge-success' }}">
                            <i class="fas {{ Auth::user()->is_banned ? 'fa-ban' : 'fa-check-circle' }} mr-1"></i>
                            {{ Auth::user()->is_banned ? 'Banni' : 'Actif' }}
                        </span>
                        <span class="badge" style="background:#eef2ff;color:#4f46e5;">
                            <i class="fas fa-star mr-1"></i>
                            Réputation : {{ Auth::user()->reputation_score >= 0 ? '+' : '' }}{{ Auth::user()->reputation_score ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Update Form -->
            <div class="section-title">
                <i class="fas fa-pen text-indigo-500"></i> Modifier mes informations
            </div>
            @include('profile.partials.update-profile-information-form')
        </div>

        <!-- Password Card -->
        <div class="glass-card" style="padding:2rem;">
            <div class="section-title">
                <i class="fas fa-lock text-amber-500"></i> Changer le mot de passe
            </div>
            @include('profile.partials.update-password-form')
        </div>

        <!-- Danger Zone -->
        <div class="glass-card" style="padding:2rem;border-top:3px solid #ef4444;">
            <div class="section-title" style="color:#ef4444;">
                <i class="fas fa-triangle-exclamation text-red-500"></i> Zone dangereuse
            </div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</x-app-layout>
