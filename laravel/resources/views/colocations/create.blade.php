<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500 font-medium">Nouvelle colocation</p>
            <h1 style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:700;color:#1e293b;">
                Créer ma Colocation 🏠
            </h1>
        </div>
    </x-slot>

    <div style="max-width:740px; margin:0 auto;">

        <!-- Steps Banner -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:2rem;">
            @foreach([
                ['num'=>'1','label'=>'Nommer votre coloc','active'=>true],
                ['num'=>'2','label'=>'Inviter des membres','active'=>false],
                ['num'=>'3','label'=>'Gérer les dépenses','active'=>false],
            ] as $step)
                <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem;border-radius:12px;
                            background:{{ $step['active'] ? '#eef2ff' : 'white' }};
                            border:{{ $step['active'] ? '2px solid #6366f1' : '1px solid #e2e8f0' }};">
                    <div style="width:32px;height:32px;border-radius:50%;
                                background:{{ $step['active'] ? '#4f46e5' : '#f1f5f9' }};
                                color:{{ $step['active'] ? 'white' : '#94a3b8' }};
                                display:flex;align-items:center;justify-content:center;
                                font-weight:700;font-size:0.875rem;flex-shrink:0;">
                        {{ $step['num'] }}
                    </div>
                    <p style="font-size:0.85rem;font-weight:{{ $step['active'] ? '600' : '500' }};
                              color:{{ $step['active'] ? '#4f46e5' : '#94a3b8' }};">{{ $step['label'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Main Card -->
        <div class="glass-card" style="padding:2.5rem;">

            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
                <div style="width:52px;height:52px;background:#eef2ff;border-radius:14px;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-house-chimney" style="color:#6366f1;font-size:1.4rem;"></i>
                </div>
                <div>
                    <h2 style="font-weight:700;color:#1e293b;font-size:1.1rem;">Informations de la colocation</h2>
                    <p style="color:#64748b;font-size:0.875rem;">Vous en deviendrez automatiquement l'Owner</p>
                </div>
            </div>

            <form action="{{ route('colocations.store') }}" method="POST">
                @csrf

                <div style="margin-bottom:1.75rem;">
                    <label for="name" style="display:block;font-size:0.875rem;font-weight:600;color:#374151;margin-bottom:0.5rem;">
                        <i class="fas fa-tag mr-1 text-indigo-400"></i>Nom de la Colocation
                    </label>
                    <input type="text" name="name" id="name" required minlength="3"
                           value="{{ old('name') }}"
                           placeholder="ex: Appart Rive Gauche, Villa des colos…"
                           style="width:100%;padding:0.875rem 1.125rem;border:1.5px solid {{ $errors->has('name') ? '#ef4444' : '#e2e8f0' }};
                                  border-radius:12px;font-size:0.95rem;outline:none;color:#1e293b;
                                  transition:all 0.2s;background:white;"
                           onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 4px rgba(99,102,241,0.1)'"
                           onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    @error('name')
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:0.4rem;">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                    <p style="color:#94a3b8;font-size:0.78rem;margin-top:0.4rem;">Entre 3 et 255 caractères</p>
                </div>

                <!-- Info box -->
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:1rem 1.25rem;margin-bottom:2rem;">
                    <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                        <i class="fas fa-circle-info text-emerald-500 mt-0.5"></i>
                        <div style="font-size:0.85rem;color:#166534;line-height:1.6;">
                            <strong>Bon à savoir :</strong> Une seule colocation active est autorisée par utilisateur.
                            Vous pourrez inviter vos colocataires par e-mail après la création.
                        </div>
                    </div>
                </div>

                <button type="submit"
                        style="width:100%;padding:0.95rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                               color:white;border:none;border-radius:12px;font-size:1rem;font-weight:700;
                               cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.5rem;"
                        onmouseover="this.style.opacity='0.9';this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
                    <i class="fas fa-plus-circle"></i> Créer ma Colocation
                </button>
            </form>
        </div>

        <!-- Bottom note -->
        <div style="text-align:center;margin-top:1.5rem;">
            <p style="color:#94a3b8;font-size:0.85rem;">
                Vous attendez une invitation ?
                <a href="{{ route('dashboard') }}" style="color:#6366f1;font-weight:600;text-decoration:none;">
                    Retour au tableau de bord
                </a>
            </p>
        </div>
    </div>

</x-app-layout>
