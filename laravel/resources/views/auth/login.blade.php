<x-guest-layout>

    <div style="margin-bottom: 2rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 0.4rem;">
            Bon retour ! 👋
        </h1>
        <p style="color: #64748b; font-size: 0.9rem;">Connectez-vous pour accéder à votre colocation</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:0.8rem 1rem; border-radius:10px; margin-bottom:1rem; font-size:0.875rem;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif

    @if ($errors->has('error'))
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:0.8rem 1rem; border-radius:10px; margin-bottom:1rem; font-size:0.875rem;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div style="margin-bottom: 1.25rem;">
            <label for="email" class="auth-label"><i class="fas fa-envelope mr-1 text-indigo-500"></i> Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="auth-input @error('email') border-red-400 @enderror"
                placeholder="vous@exemple.com">
            @error('email')
                <p style="color:#ef4444; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div style="margin-bottom: 1.25rem;">
            <label for="password" class="auth-label"><i class="fas fa-lock mr-1 text-indigo-500"></i> Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="auth-input @error('password') border-red-400 @enderror"
                placeholder="••••••••">
            @error('password')
                <p style="color:#ef4444; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me + Forgot -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.875rem; color:#4b5563;">
                <input type="checkbox" id="remember_me" name="remember"
                    style="width:16px; height:16px; accent-color:#4f46e5;">
                Se souvenir de moi
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    style="font-size:0.85rem; color:#6366f1; font-weight:500; text-decoration:none; transition:color 0.2s;"
                    onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#6366f1'">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button type="submit" class="auth-btn">
            <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
        </button>

        <p style="text-align:center; margin-top:1.5rem; font-size:0.875rem; color:#6b7280;">
            Pas encore de compte ?
            <a href="{{ route('register') }}"
                style="color:#6366f1; font-weight:600; text-decoration:none;"
                onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                Créer un compte
            </a>
        </p>
    </form>

</x-guest-layout>
