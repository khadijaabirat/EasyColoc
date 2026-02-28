<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500 font-medium">Bienvenue,</p>
            <h1 style="font-family:'Outfit',sans-serif; font-size:1.75rem; font-weight:700; color:#1e293b; line-height:1.2;">
                {{ Auth::user()->name }} <span style="font-size:1.5rem;">👋</span>
            </h1>
        </div>
    </x-slot>

    @php
        $activeColoc = auth()->user()->colocations()->wherePivotNull('left_at')->first();
        $reputation  = auth()->user()->reputation_score ?? 0;
    @endphp

    {{-- HERO: No active colocation --}}
    @if(!$activeColoc)
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-bottom:2rem;">

            <!-- Create CTA Card -->
            <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);
                        border-radius:20px; padding:3rem; color:white; position:relative; overflow:hidden;">
                <div style="position:absolute; top:-60px; right:-60px; width:250px; height:250px;
                             border-radius:50%; background:rgba(255,255,255,0.08);"></div>
                <div style="position:relative; z-index:1; max-width:600px;">
                    <span style="background:rgba(255,255,255,0.2); padding:0.35rem 0.9rem; border-radius:9999px; font-size:0.78rem; font-weight:600; letter-spacing:0.05em;">
                        AUCUNE COLOCATION ACTIVE
                    </span>
                    <h2 style="font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:700; margin:1rem 0 0.75rem;">
                        Nouvelle colocation
                    </h2>
                    <p style="opacity:0.85; margin-bottom:2rem; line-height:1.7;">
                        Créez une colocation et invitez vos colocataires à vous rejoindre.
                    </p>
                    <a href="{{ route('colocations.create') }}"
                       style="display:inline-flex; align-items:center; gap:0.5rem; background:white; color:#4f46e5;
                              padding:0.875rem 1.75rem; border-radius:12px; font-weight:700; text-decoration:none;
                              transition:all 0.2s; box-shadow:0 4px 14px rgba(0,0,0,0.2);">
                        <i class="fas fa-plus-circle"></i> Créer ma Colocation
                    </a>
                </div>
            </div>

            <!-- Join CTA Card -->
            <div style="background:white; border:2px solid #e2e8f0;
                        border-radius:20px; padding:3rem; color:#1e293b; position:relative;">
                <span style="background:#f1f5f9; color:#64748b; padding:0.35rem 0.9rem; border-radius:9999px; font-size:0.78rem; font-weight:600; letter-spacing:0.05em;">
                    CODE D'INVITATION
                </span>
                <h2 style="font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:700; margin:1rem 0 0.75rem;">
                    Rejoindre une colocation
                </h2>
                <p style="color:#64748b; margin-bottom:1.5rem; line-height:1.7;">
                    Vous avez reçu un code ou un token d'invitation ? Entrez-le ci-dessous.
                </p>
                <form action="{{ route('invitations.join') }}" method="POST" style="display:flex; flex-direction:column; gap:1rem;">
                    @csrf
                    <input type="text" name="token" required placeholder="Ex: a1b2c3d4e5..." 
                           style="padding:0.875rem 1rem; border:1.5px solid #e2e8f0; border-radius:12px; font-size:1rem; outline:none; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                    <button type="submit" 
                            style="padding:0.875rem 1.75rem; background:#10b981; color:white; border:none; border-radius:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;"
                            onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        <i class="fas fa-sign-in-alt"></i> Rejoindre la colocation
                    </button>
                </form>
            </div>
        </div>

        <!-- Info Cards -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem;">
            @foreach([
                ['icon'=>'fa-users', 'color'=>'#6366f1', 'bg'=>'#eef2ff', 'title'=>'Invitez vos colocataires', 'desc'=>'Envoyez des invitations par e-mail directement depuis la plateforme.'],
                ['icon'=>'fa-receipt', 'color'=>'#10b981', 'bg'=>'#ecfdf5', 'title'=>'Gérez les dépenses', 'desc'=>'Ajoutez, organisez et suivez toutes les dépenses partagées.'],
                ['icon'=>'fa-scale-balanced', 'color'=>'#f59e0b', 'bg'=>'#fffbeb', 'title'=>'Soldez vos comptes', 'desc'=>'Calcul automatique des remboursements entre colocataires.'],
            ] as $card)
                <div class="glass-card" style="padding:1.75rem; border-top:3px solid {{ $card['color'] }};">
                    <div style="width:48px; height:48px; background:{{ $card['bg'] }}; border-radius:12px;
                                display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                        <i class="fas {{ $card['icon'] }}" style="color:{{ $card['color'] }}; font-size:1.2rem;"></i>
                    </div>
                    <h3 style="font-weight:700; color:#1e293b; margin-bottom:0.5rem;">{{ $card['title'] }}</h3>
                    <p style="color:#64748b; font-size:0.875rem; line-height:1.6;">{{ $card['desc'] }}</p>
                </div>
            @endforeach
        </div>

    {{-- DASHBOARD: Has active colocation --}}
    @else
        @php
            $totalMembers  = $activeColoc->members()->wherePivotNull('left_at')->count();
            $totalExpenses = $activeColoc->expenses()->count();
            $totalAmount   = $activeColoc->expenses()->sum('amount');
            $myShare       = $totalMembers > 0 ? $totalAmount / $totalMembers : 0;
            $iPaid         = $activeColoc->expenses()->where('payer_id', auth()->id())->sum('amount');
            $myBalance     = $iPaid - $myShare;
            $isOwner       = $activeColoc->members()->where('user_id', auth()->id())->wherePivot('role','owner')->exists();
        @endphp

        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; margin-bottom:2rem;">
            @foreach([
                ['label'=>'Membres actifs', 'value'=>$totalMembers, 'icon'=>'fa-users', 'color'=>'#6366f1', 'bg'=>'#eef2ff'],
                ['label'=>'Dépenses totales', 'value'=>$totalExpenses, 'icon'=>'fa-receipt', 'color'=>'#10b981', 'bg'=>'#ecfdf5'],
                ['label'=>'Montant total', 'value'=>number_format($totalAmount,2).' €', 'icon'=>'fa-euro-sign', 'color'=>'#f59e0b', 'bg'=>'#fffbeb'],
                ['label'=>'Mon solde', 'value'=>($myBalance >= 0 ? '+' : '').number_format($myBalance,2).' €', 'icon'=>'fa-wallet', 'color'=>($myBalance >= 0 ? '#10b981' : '#ef4444'), 'bg'=>($myBalance >= 0 ? '#ecfdf5' : '#fef2f2')],
            ] as $stat)
                <div class="stat-card" style="border-top:3px solid {{ $stat['color'] }}; transition:all 0.2s;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                        <div>
                            <p class="stat-label">{{ $stat['label'] }}</p>
                            <p class="stat-value" style="color:{{ $stat['color'] }};">{{ $stat['value'] }}</p>
                        </div>
                        <div style="width:42px; height:42px; background:{{ $stat['bg'] }}; border-radius:10px;
                                    display:flex; align-items:center; justify-content:center;">
                            <i class="fas {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Coloc Card + Quick Actions -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-bottom:2rem;">

            <!-- Coloc Hero -->
            <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); border-radius:20px; padding:2.5rem; color:white; position:relative; overflow:hidden;">
                <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
                <div style="position:relative;z-index:1;">
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
                        <span style="background:rgba(255,255,255,0.2);padding:0.3rem 0.8rem;border-radius:9999px;font-size:0.75rem;font-weight:600;">
                            {{ strtoupper($activeColoc->status) }}
                        </span>
                        @if($isOwner)
                            <span style="background:rgba(251,191,36,0.3);color:#fde68a;padding:0.3rem 0.8rem;border-radius:9999px;font-size:0.75rem;font-weight:600;">
                                <i class="fas fa-crown mr-1"></i>OWNER
                            </span>
                        @endif
                    </div>
                    <h2 style="font-family:'Outfit',sans-serif;font-size:2rem;font-weight:700;margin-bottom:0.5rem;">
                        {{ $activeColoc->name }}
                    </h2>
                    <p style="opacity:0.8;margin-bottom:2rem;font-size:0.9rem;">
                        {{ $totalMembers }} membre(s) · Créée le {{ $activeColoc->created_at->format('d/m/Y') }}
                    </p>
                    <a href="{{ route('colocations.show', $activeColoc) }}"
                       style="display:inline-flex;align-items:center;gap:0.5rem;background:white;color:#4f46e5;
                              padding:0.75rem 1.5rem;border-radius:12px;font-weight:700;text-decoration:none;">
                        <i class="fas fa-arrow-right"></i> Accéder à ma Colocation
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div class="glass-card" style="padding:1.5rem;">
                    <p style="font-size:0.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:1rem;">Actions rapides</p>
                    <a href="{{ route('colocations.show', $activeColoc) }}"
                       style="display:flex;align-items:center;gap:0.875rem;padding:0.75rem;border-radius:10px;
                              background:#f8fafc;text-decoration:none;color:#1e293b;font-weight:500;
                              margin-bottom:0.5rem;transition:all 0.2s;"
                       onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        <div style="width:36px;height:36px;background:#eef2ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-users" style="color:#6366f1;font-size:0.85rem;"></i>
                        </div>
                        Voir les membres
                    </a>
                    <a href="{{ route('colocations.show', $activeColoc) }}"
                       style="display:flex;align-items:center;gap:0.875rem;padding:0.75rem;border-radius:10px;
                              background:#f8fafc;text-decoration:none;color:#1e293b;font-weight:500;
                              margin-bottom:0.5rem;transition:all 0.2s;"
                       onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        <div style="width:36px;height:36px;background:#ecfdf5;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-plus" style="color:#10b981;font-size:0.85rem;"></i>
                        </div>
                        Ajouter une dépense
                    </a>
                </div>

                <!-- Reputation -->
                <div class="glass-card" style="padding:1.5rem; text-align:center;">
                    <p style="font-size:0.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.75rem;">Ma Réputation</p>
                    <div style="font-size:2.5rem;margin-bottom:0.25rem;">
                        {{ $reputation >= 0 ? '😊' : '😟' }}
                    </div>
                    <p style="font-size:1.5rem;font-weight:700;color:{{ $reputation >= 0 ? '#10b981' : '#ef4444' }};">
                        {{ $reputation >= 0 ? '+' : '' }}{{ $reputation }}
                    </p>
                    <p style="font-size:0.75rem;color:#94a3b8;margin-top:0.25rem;">Score de réputation</p>
                </div>
            </div>
        </div>

        <!-- Members Preview -->
        <div class="glass-card" style="padding:1.75rem;">
            <div class="section-title">
                <i class="fas fa-users text-indigo-500"></i> Membres de {{ $activeColoc->name }}
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:1rem;">
                @foreach($activeColoc->members()->wherePivotNull('left_at')->get() as $member)
                    <div style="display:flex;align-items:center;gap:0.75rem;background:#f8fafc;
                                padding:0.75rem 1.25rem;border-radius:12px;border:1px solid #e2e8f0;">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background={{ $member->pivot->role === 'owner' ? 'fbbf24' : '6366f1' }}&color=fff"
                             style="width:38px;height:38px;border-radius:50%;" alt="{{ $member->name }}">
                        <div>
                            <p style="font-weight:600;font-size:0.875rem;color:#1e293b;">{{ $member->name }}</p>
                            <p style="font-size:0.75rem;color:#64748b;">
                                @if($member->pivot->role === 'owner')
                                    <i class="fas fa-crown text-amber-500 mr-1"></i>Owner
                                @else
                                    <i class="fas fa-user text-indigo-400 mr-1"></i>Membre
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</x-app-layout>
