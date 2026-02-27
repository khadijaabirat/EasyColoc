<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500 font-medium">Administration</p>
            <h1 style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:0.75rem;">
                <i class="fas fa-shield-halved" style="color:#ef4444;"></i> Panneau d'administration
            </h1>
        </div>
    </x-slot>

    {{-- Global Stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:2rem;">
        @foreach([
            ['label'=>'Utilisateurs total','value'=>$stats['total_users'],'icon'=>'fa-users','color'=>'#6366f1','bg'=>'#eef2ff'],
            ['label'=>'Utilisateurs bannis','value'=>$stats['banned_users'],'icon'=>'fa-ban','color'=>'#ef4444','bg'=>'#fef2f2'],
            ['label'=>'Colocations total','value'=>$stats['total_colocations'],'icon'=>'fa-house','color'=>'#10b981','bg'=>'#ecfdf5'],
            ['label'=>'Colocations actives','value'=>$stats['active_colocations'],'icon'=>'fa-check-circle','color'=>'#f59e0b','bg'=>'#fffbeb'],
            ['label'=>'Dépenses total','value'=>$stats['total_expenses'],'icon'=>'fa-receipt','color'=>'#a855f7','bg'=>'#f5f3ff'],
            ['label'=>'Montant échangé','value'=>number_format($stats['total_amount'],2).' €','icon'=>'fa-euro-sign','color'=>'#0ea5e9','bg'=>'#f0f9ff'],
        ] as $s)
            <div class="stat-card" style="border-top:3px solid {{ $s['color'] }};">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <p class="stat-label">{{ $s['label'] }}</p>
                        <p class="stat-value" style="color:{{ $s['color'] }};font-size:1.6rem;">{{ $s['value'] }}</p>
                    </div>
                    <div style="width:42px;height:42px;background:{{ $s['bg'] }};border-radius:10px;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }};"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.75rem;">

        {{-- Users Table --}}
        <div class="glass-card" style="padding:1.75rem;">
            <div class="section-title">
                <i class="fas fa-users text-indigo-500"></i> Gestion des Utilisateurs
            </div>

            {{-- Search / Pagination info --}}
            <p style="font-size:0.8rem;color:#94a3b8;margin-bottom:1rem;">
                {{ $users->total() }} utilisateur(s) au total · Page {{ $users->currentPage() }} / {{ $users->lastPage() }}
            </p>

            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Réputation</th>
                        <th>Statut</th>
                        <th>Inscrit le</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'"
                            style="transition:background 0.15s;">
                            <td>
                                <div style="display:flex;align-items:center;gap:0.75rem;">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->is_banned ? 'ef4444' : '6366f1' }}&color=fff"
                                         style="width:36px;height:36px;border-radius:50%;" alt="">
                                    <div>
                                        <p style="font-weight:600;font-size:0.875rem;color:#1e293b;">{{ $user->name }}</p>
                                        <p style="font-size:0.75rem;color:#94a3b8;">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-danger"><i class="fas fa-shield-halved mr-1"></i>Admin</span>
                                @else
                                    <span class="badge badge-info"><i class="fas fa-user mr-1"></i>User</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:700;color:{{ ($user->reputation_score ?? 0) >= 0 ? '#10b981' : '#ef4444' }};">
                                    {{ ($user->reputation_score ?? 0) >= 0 ? '+' : '' }}{{ $user->reputation_score ?? 0 }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_banned)
                                    <span class="badge badge-danger"><i class="fas fa-ban mr-1"></i>Banni</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Actif</span>
                                @endif
                            </td>
                            <td style="color:#64748b;font-size:0.85rem;">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td style="text-align:center;">
                                @if($user->id !== auth()->id() && $user->role !== 'admin')
                                    @if(!$user->is_banned)
                                        <form action="{{ route('admin.users.ban', $user) }}" method="POST"
                                              style="display:inline;" onsubmit="return confirm('Bannir {{ $user->name }} ?')">
                                            @csrf
                                            <button type="submit"
                                                    style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;
                                                           padding:0.35rem 0.875rem;border-radius:8px;font-size:0.78rem;
                                                           font-weight:600;cursor:pointer;transition:all 0.2s;"
                                                    onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                                <i class="fas fa-ban mr-1"></i>Bannir
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.unban', $user) }}" method="POST"
                                              style="display:inline;">
                                            @csrf
                                            <button type="submit"
                                                    style="background:#ecfdf5;color:#10b981;border:1px solid #bbf7d0;
                                                           padding:0.35rem 0.875rem;border-radius:8px;font-size:0.78rem;
                                                           font-weight:600;cursor:pointer;transition:all 0.2s;"
                                                    onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#ecfdf5'">
                                                <i class="fas fa-unlock-alt mr-1"></i>Débannir
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span style="font-size:0.75rem;color:#cbd5e1;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div style="margin-top:1.5rem;">
                {{ $users->links() }}
            </div>
        </div>

        {{-- Colocations Overview --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <div class="glass-card" style="padding:1.75rem;">
                <div class="section-title">
                    <i class="fas fa-house text-emerald-500"></i> Colocations
                </div>
                @forelse($colocations->take(10) as $coloc)
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:0.75rem;border-radius:10px;background:#f8fafc;
                                border:1px solid #e2e8f0;margin-bottom:0.5rem;">
                        <div>
                            <p style="font-weight:600;font-size:0.875rem;color:#1e293b;">{{ $coloc->name }}</p>
                            <p style="font-size:0.75rem;color:#94a3b8;">
                                {{ $coloc->members->count() }} membre(s) · {{ $coloc->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="badge {{ $coloc->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($coloc->status) }}
                        </span>
                    </div>
                @empty
                    <p style="color:#94a3b8;font-size:0.875rem;text-align:center;padding:2rem;">
                        Aucune colocation enregistrée.
                    </p>
                @endforelse

                @if($colocations->count() > 10)
                    <p style="font-size:0.78rem;color:#94a3b8;text-align:center;margin-top:0.75rem;">
                        + {{ $colocations->count() - 10 }} autre(s) colocation(s)
                    </p>
                @endif
            </div>

            {{-- Quick info --}}
            <div class="glass-card" style="padding:1.75rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border:none;">
                <div style="font-size:0.75rem;font-weight:700;opacity:0.7;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:1rem;">
                    Administration
                </div>
                <p style="font-size:0.9rem;opacity:0.9;line-height:1.7;margin-bottom:1rem;">
                    Connecté en tant qu'<strong>Administrateur Global</strong>.<br>
                    Vous pouvez bannir ou débannir des utilisateurs.
                </p>
                <div style="font-size:0.8rem;opacity:0.7;">
                    Dernière connexion : {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
