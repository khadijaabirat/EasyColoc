<x-guest-layout>

    <div style="margin-bottom: 2rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 0.4rem;">
            Créer un compte 🏠
        </h1>
        <p style="color: #64748b; font-size: 0.9rem;">Rejoignez EasyColoc et gérez votre colocation facilement</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div style="margin-bottom: 1.25rem;">
            <label for="name" class="auth-label"><i class="fas fa-user mr-1 text-indigo-500"></i> Prénom & Nom</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                class="auth-input @error('name') border-red-400 @enderror"
                placeholder="Jean Dupont">
            @error('name')
                <p style="color:#ef4444; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div style="margin-bottom: 1.25rem;">
            <label for="email" class="auth-label"><i class="fas fa-envelope mr-1 text-indigo-500"></i> Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="auth-input @error('email') border-red-400 @enderror"
                placeholder="vous@exemple.com">
            @error('email')
                <p style="color:#ef4444; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div style="margin-bottom: 1.25rem;">
            <label for="password" class="auth-label"><i class="fas fa-lock mr-1 text-indigo-500"></i> Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="auth-input @error('password') border-red-400 @enderror"
                placeholder="Minimum 8 caractères">
            @error('password')
                <p style="color:#ef4444; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div style="margin-bottom: 1.75rem;">
            <label for="password_confirmation" class="auth-label"><i class="fas fa-lock-open mr-1 text-indigo-500"></i> Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="auth-input @error('password_confirmation') border-red-400 @enderror"
                placeholder="Répétez le mot de passe">
            @error('password_confirmation')
                <p style="color:#ef4444; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-user-plus mr-2"></i>Créer mon compte
        </button>

        <p style="text-align:center; margin-top:1.5rem; font-size:0.875rem; color:#6b7280;">
            Déjà inscrit ?
            <a href="{{ route('login') }}"
                style="color:#6366f1; font-weight:600; text-decoration:none;"
                onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                Se connecter
            </a>
        </p>
    </form>

</x-guest-layout>
