<x-app-layout>
    @php
        $isOwner = $colocation->members()->where('user_id', auth()->id())->wherePivot('role','owner')->exists();
        $activeMembers = $colocation->members()->wherePivotNull('left_at')->get();
        $memberCount   = $activeMembers->count();

        // Balance calculation
        $totalAmount = $colocation->expenses->sum('amount');
        $share       = $memberCount > 0 ? $totalAmount / $memberCount : 0;

        $paid = [];
        foreach($activeMembers as $m) {
            $paid[$m->id] = $colocation->expenses->where('payer_id', $m->id)->sum('amount');
        }

        $balances = [];
        foreach($activeMembers as $m) {
            $balances[$m->id] = round($paid[$m->id] - $share, 2);
        }
    @endphp

    <x-slot name="header">
        <div>
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.25rem;">
                <a href="{{ route('dashboard') }}" style="color:#94a3b8;text-decoration:none;font-size:0.875rem;display:flex;align-items:center;gap:0.35rem;">
                    <i class="fas fa-arrow-left"></i> Tableau de bord
                </a>
                <span style="color:#cbd5e1;">/</span>
                <span style="color:#94a3b8;font-size:0.875rem;">{{ $colocation->name }}</span>
            </div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:0.75rem;">
                {{ $colocation->name }}
                <span style="font-size:0.875rem;padding:0.35rem 0.9rem;border-radius:9999px;font-weight:600;
                             background:{{ $colocation->status === 'active' ? '#d1fae5' : '#fee2e2' }};
                             color:{{ $colocation->status === 'active' ? '#065f46' : '#991b1b' }};">
                    {{ ucfirst($colocation->status) }}
                </span>
            </h1>
        </div>
    </x-slot>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:1.75rem;">

        {{-- LEFT COLUMN --}}
        <div style="display:flex;flex-direction:column;gap:1.75rem;">

            {{-- Stats Row --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                @foreach([
                    ['label'=>'Membres actifs','value'=>$memberCount,'icon'=>'fa-users','color'=>'#6366f1','bg'=>'#eef2ff'],
                    ['label'=>'Dépenses','value'=>$colocation->expenses->count(),'icon'=>'fa-receipt','color'=>'#10b981','bg'=>'#ecfdf5'],
                    ['label'=>'Total dépensé','value'=>number_format($totalAmount,2).' €','icon'=>'fa-euro-sign','color'=>'#f59e0b','bg'=>'#fffbeb'],
                ] as $s)
                    <div class="stat-card" style="border-top:3px solid {{ $s['color'] }};">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                            <div>
                                <p class="stat-label">{{ $s['label'] }}</p>
                                <p class="stat-value" style="color:{{ $s['color'] }};font-size:1.5rem;">{{ $s['value'] }}</p>
                            </div>
                            <div style="width:40px;height:40px;background:{{ $s['bg'] }};border-radius:10px;
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }};"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Members List --}}
            <div class="glass-card" style="padding:1.75rem;">
                <div class="section-title">
                    <i class="fas fa-users text-indigo-500"></i> Membres de la Colocation
                </div>
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    @foreach($activeMembers as $member)
                        @php
                            $bal = $balances[$member->id] ?? 0;
                            $memberReputation = $member->reputation_score ?? 0;
                        @endphp
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;
                                    background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;
                                    transition:all 0.2s;"
                             onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background={{ $member->pivot->role === 'owner' ? 'fbbf24' : '6366f1' }}&color=fff"
                                     style="width:44px;height:44px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.1);"
                                     alt="{{ $member->name }}">
                                <div>
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <p style="font-weight:600;color:#1e293b;font-size:0.925rem;">{{ $member->name }}</p>
                                        @if($member->pivot->role === 'owner')
                                            <span style="background:#fef3c7;color:#92400e;padding:0.2rem 0.6rem;border-radius:9999px;font-size:0.7rem;font-weight:700;">
                                                <i class="fas fa-crown mr-1"></i>OWNER
                                            </span>
                                        @endif
                                    </div>
                                    <p style="font-size:0.78rem;color:#94a3b8;">
                                        {{ $member->email }} · Réputation: {{ $memberReputation >= 0 ? '+' : '' }}{{ $memberReputation }}
                                    </p>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <div style="text-align:right;">
                                    <p style="font-weight:700;font-size:1rem;color:{{ $bal >= 0 ? '#10b981' : '#ef4444' }};">
                                        {{ $bal >= 0 ? '+' : '' }}{{ number_format($bal, 2) }} €
                                    </p>
                                    <p style="font-size:0.75rem;color:#94a3b8;">solde</p>
                                </div>
                                @if($isOwner && $colocation->status === 'active' && $member->pivot->role !== 'owner')
                                    <form action="{{ route('memberships.remove', [$colocation, $member]) }}" method="POST"
                                          onsubmit="return confirm('Retirer {{ $member->name }} de la colocation ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                style="background:#fef2f2;border:1px solid #fecaca;color:#ef4444;
                                                       padding:0.4rem 0.75rem;border-radius:8px;cursor:pointer;
                                                       font-size:0.78rem;font-weight:600;transition:all 0.2s;"
                                                title="Retirer ce membre">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Expenses --}}
            <div class="glass-card" style="padding:1.75rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
                    <div class="section-title" style="margin-bottom:0;">
                        <i class="fas fa-receipt text-emerald-500"></i> Historique des Dépenses
                    </div>
                    <div style="display:flex;gap:1rem;align-items:center;">
                        <form method="GET" action="{{ route('colocations.show', $colocation) }}" style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="month" name="month" value="{{ $monthFilter }}" onchange="this.form.submit()" 
                                   style="padding:0.4rem 0.8rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.875rem;outline:none;color:#1e293b;">
                            @if($monthFilter)
                                <a href="{{ route('colocations.show', $colocation) }}" style="color:#ef4444;text-decoration:none;" title="Réinitialiser"><i class="fas fa-times-circle"></i></a>
                            @endif
                        </form>
                        @if($colocation->status === 'active')
                            <button onclick="document.getElementById('expense-modal').style.display='flex'"
                                    style="display:flex;align-items:center;gap:0.5rem;background:#4f46e5;color:white;
                                           border:none;padding:0.6rem 1.2rem;border-radius:10px;font-weight:600;cursor:pointer;
                                           font-size:0.875rem;transition:all 0.2s;"
                                    onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                                <i class="fas fa-plus"></i> Ajouter
                            </button>
                        @endif
                    </div>
                </div>

                @if($filteredExpenses->isEmpty())
                    <div style="text-align:center;padding:3rem;color:#94a3b8;">
                        <i class="fas fa-receipt" style="font-size:3rem;margin-bottom:1rem;opacity:0.3;"></i>
                        <p style="font-weight:500;">Aucune dépense enregistrée</p>
                        <p style="font-size:0.875rem;margin-top:0.25rem;">Ajoutez votre première dépense partagée</p>
                    </div>
                @else
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Dépense</th>
                                <th>Catégorie</th>
                                <th>Payeur</th>
                                <th>Date</th>
                                <th style="text-align:right;">Montant</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($filteredExpenses as $expense)
                                <tr style="transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="font-weight:600;color:#1e293b;">{{ $expense->title }}</td>
                                    <td>
                                        @if($expense->category)
                                            <span class="badge badge-info">{{ $expense->category->name ?? 'N/A' }}</span>
                                        @else
                                            <span class="badge badge-warning">Sans catégorie</span>
                                        @endif
                                    </td>
                                    <td style="color:#64748b;">
                                        @php $payer = $activeMembers->firstWhere('id', $expense->payer_id); @endphp
                                        <div style="display:flex;align-items:center;gap:0.5rem;">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($payer?->name ?? '?') }}&background=6366f1&color=fff&size=24"
                                                 style="width:24px;height:24px;border-radius:50%;" alt="">
                                            {{ $payer?->name ?? 'Inconnu' }}
                                        </div>
                                    </td>
                                    <td style="color:#64748b;">{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                                    <td style="text-align:right;font-weight:700;color:#1e293b;">
                                        {{ number_format($expense->amount, 2) }} €
                                    </td>
                                    <td style="text-align:right;">
                                        @if($colocation->status === 'active' && ($expense->payer_id === auth()->id() || $isOwner))
                                            <div style="display:flex;gap:0.25rem;justify-content:flex-end;">
                                                <button onclick="openEditModal({{ $expense->id }}, '{{ addslashes($expense->title) }}', {{ $expense->amount }}, '{{ $expense->date }}', {{ $expense->payer_id }}, '{{ $expense->category_id }}')" 
                                                        style="background:none;border:none;cursor:pointer;color:#4f46e5;padding:0.3rem;" 
                                                        title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('expenses.destroy', [$colocation, $expense]) }}" method="POST"
                                                      onsubmit="return confirm('Supprimer cette dépense ?')" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            style="background:none;border:none;cursor:pointer;color:#ef4444;padding:0.3rem;"
                                                            title="Supprimer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem;">

            {{-- Invite Section --}}
            @if($isOwner && $colocation->status === 'active')
                <div class="glass-card" style="padding:1.75rem;border-top:3px solid #10b981;">
                    <div class="section-title">
                        <i class="fas fa-envelope text-emerald-500"></i> Inviter un membre
                    </div>
                    
                    @if(session('generated_token'))
                        <div style="margin-bottom:1.5rem; padding:1.25rem; background:#ecfdf5; border:1.5px dashed #10b981; border-radius:12px; color:#065f46;">
                            <p style="font-size:0.875rem; font-weight:700; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem;">
                                <i class="fas fa-key"></i> Code d'invitation
                            </p>
                            <div style="padding:0.75rem; background:#ffffff; border-radius:8px; border:1px solid #a7f3d0; font-family:monospace; font-size:1rem; font-weight:bold; letter-spacing:0.05em; word-break:break-all; text-align:center; color:#047857; box-shadow:0 2px 4px rgba(16, 185, 129, 0.1);">
                                {{ session('generated_token') }}
                            </div>
                            <p style="font-size:0.8rem; margin-top:0.75rem; opacity:0.9; line-height:1.4;">
                                Vous pouvez simplement partager ce code avec le futur colocataire. Il pourra le coller sur son tableau de bord pour vous rejoindre.
                            </p>
                        </div>
                    @endif
                    <form action="{{ route('invitations.store', $colocation) }}" method="POST">
                        @csrf
                        <div style="margin-bottom:1rem;">
                            <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">
                                Adresse e-mail
                            </label>
                            <input type="email" name="email" required placeholder="ami@exemple.com"
                                   style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;
                                          font-size:0.875rem;outline:none;color:#1e293b;transition:all 0.2s;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#10b981';this.style.boxShadow='0 0 0 4px rgba(16,185,129,0.1)'"
                                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                        </div>
                        <button type="submit"
                                style="width:100%;padding:0.75rem;background:#10b981;color:white;border:none;border-radius:10px;
                                       font-weight:600;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.5rem;"
                                onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                            <i class="fas fa-paper-plane"></i> Envoyer l'invitation
                        </button>
                    </form>
                </div>
            @endif

            {{-- Settlements/Balances --}}
            <div class="glass-card" style="padding:1.75rem;border-top:3px solid #f59e0b;">
                <div class="section-title">
                    <i class="fas fa-scale-balanced text-amber-500"></i> Qui doit à qui ?
                </div>
                {{-- Settlements from DB --}}
                @php
                    $dbSettlements = $colocation->settlements()->where('is_paid', false)
                        ->with(['debtor','creditor'])->get();
                @endphp

                @if($dbSettlements->isEmpty())
                    <div style="text-align:center;padding:2rem;color:#94a3b8;">
                        <i class="fas fa-check-circle" style="font-size:2.5rem;color:#10b981;margin-bottom:0.75rem;display:block;"></i>
                        <p style="font-weight:500;color:#374151;">Tout est réglé !</p>
                        <p style="font-size:0.8rem;margin-top:0.25rem;">Aucun remboursement nécessaire</p>
                    </div>
                @else
                    @foreach($dbSettlements as $dbS)
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;
                                    padding:0.875rem;margin-bottom:0.75rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <div style="font-size:0.875rem;color:#1e293b;">
                                    <span style="font-weight:600;color:#ef4444;">{{ $dbS->debtor->name }}</span>
                                    <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                    <span style="font-weight:600;color:#10b981;">{{ $dbS->creditor->name }}</span>
                                    <span style="font-weight:700;color:#92400e;margin-left:0.75rem;">{{ number_format($dbS->amount,2) }} €</span>
                                </div>
                                @if($dbS->debtor_id === auth()->id() || $isOwner)
                                    <form action="{{ route('settlements.pay', [$colocation, $dbS]) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                style="background:{{ $isOwner && $dbS->debtor_id !== auth()->id() ? '#f59e0b' : '#4f46e5' }};color:white;border:none;padding:0.4rem 0.9rem;
                                                       border-radius:8px;font-size:0.78rem;font-weight:600;cursor:pointer;"
                                                onmouseover="this.style.background='{{ $isOwner && $dbS->debtor_id !== auth()->id() ? '#d97706' : '#4338ca' }}'" 
                                                onmouseout="this.style.background='{{ $isOwner && $dbS->debtor_id !== auth()->id() ? '#f59e0b' : '#4f46e5' }}'"
                                                title="{{ $isOwner && $dbS->debtor_id !== auth()->id() ? 'En tant que propriétaire, vous pouvez marquer ceci comme payé.' : '' }}">
                                            <i class="fas fa-check mr-1"></i>Marquer payé
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Categories Management (Owner only) --}}
            @if($isOwner && $colocation->status === 'active')
                <div class="glass-card" style="padding:1.75rem;border-top:3px solid #a855f7;">
                    <div class="section-title">
                        <i class="fas fa-tags" style="color:#a855f7;"></i> Catégories
                    </div>

                    {{-- Existing categories --}}
                    @if($colocation->categories->isNotEmpty())
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                            @foreach($colocation->categories as $cat)
                                <div style="display:flex;align-items:center;gap:0.4rem;background:#f5f3ff;
                                            border:1px solid #ddd6fe;border-radius:9999px;
                                            padding:0.3rem 0.75rem;font-size:0.8rem;font-weight:600;color:#6d28d9;">
                                    {{ $cat->name }}
                                    <form action="{{ route('categories.destroy', [$colocation, $cat]) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette catégorie ?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                style="background:none;border:none;cursor:pointer;color:#a855f7;
                                                       padding:0;line-height:1;font-size:0.75rem;"
                                                title="Supprimer">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size:0.8rem;color:#94a3b8;margin-bottom:1rem;">Aucune catégorie définie.</p>
                    @endif

                    {{-- Add category form --}}
                    <form action="{{ route('categories.store', $colocation) }}" method="POST"
                          style="display:flex;gap:0.5rem;">
                        @csrf
                        <input type="text" name="name" required placeholder="Nouvelle catégorie…"
                               style="flex:1;padding:0.6rem 0.875rem;border:1.5px solid #e2e8f0;border-radius:10px;
                                      font-size:0.85rem;outline:none;color:#1e293b;"
                               onfocus="this.style.borderColor='#a855f7'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="submit"
                                style="background:#a855f7;color:white;border:none;padding:0.6rem 1rem;
                                       border-radius:10px;font-weight:600;cursor:pointer;white-space:nowrap;
                                       font-size:0.85rem;transition:all 0.2s;"
                                onmouseover="this.style.background='#9333ea'" onmouseout="this.style.background='#a855f7'">
                            <i class="fas fa-plus"></i>
                        </button>
                    </form>
                </div>
            @endif

            {{-- Owner Actions --}}
            @if($isOwner && $colocation->status === 'active')
                <div class="glass-card" style="padding:1.75rem;border-top:3px solid #6366f1;">
                    <p style="font-size:0.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:1rem;">
                        Gestion de la Colocation
                    </p>
                    <form action="{{ route('colocations.cancel', $colocation) }}" method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir annuler définitivement cette colocation ?')">
                        @csrf
                        <button type="submit"
                                style="width:100%;padding:0.75rem;background:white;color:#ef4444;border:2px solid #ef4444;
                                       border-radius:10px;font-weight:600;cursor:pointer;transition:all 0.2s;
                                       display:flex;align-items:center;justify-content:center;gap:0.5rem;"
                                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                            <i class="fas fa-ban"></i> Annuler la Colocation
                        </button>
                    </form>
                </div>
            @endif

            {{-- Member Leave --}}
            @if(!$isOwner && $colocation->status === 'active')
                <div class="glass-card" style="padding:1.75rem;">
                    <form action="{{ route('colocations.leave', $colocation) }}" method="POST"
                          onsubmit="return confirm('Quitter cette colocation ?')">
                        @csrf
                        <button type="submit"
                                style="width:100%;padding:0.75rem;background:white;color:#f59e0b;border:2px solid #f59e0b;
                                       border-radius:10px;font-weight:600;cursor:pointer;transition:all 0.2s;
                                       display:flex;align-items:center;justify-content:center;gap:0.5rem;"
                                onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='white'">
                            <i class="fas fa-door-open"></i> Quitter la Colocation
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Add Expense Modal --}}
    @if($colocation->status === 'active')
        <div id="expense-modal"
             style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;
                    align-items:center;justify-content:center;backdrop-filter:blur(4px);">
            <div style="background:white;border-radius:20px;padding:2.5rem;width:100%;max-width:500px;
                        box-shadow:0 25px 50px rgba(0,0,0,0.2);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.75rem;">
                    <h3 style="font-family:'Outfit',sans-serif;font-size:1.3rem;font-weight:700;color:#1e293b;">
                        <i class="fas fa-plus-circle text-indigo-500 mr-2"></i>Nouvelle dépense
                    </h3>
                    <button onclick="document.getElementById('expense-modal').style.display='none'"
                            style="background:#f1f5f9;border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;
                                   font-size:1rem;color:#64748b;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('expenses.store', $colocation) }}" method="POST">
                    @csrf
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Titre de la dépense</label>
                        <input type="text" name="title" required placeholder="ex: Courses Lidl, Loyer, Électricité…"
                               style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                        <div>
                            <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Montant (€)</label>
                            <input type="number" name="amount" required min="0.01" step="0.01" placeholder="0.00"
                                   style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Date</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                                   style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                    </div>
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Payeur</label>
                        @if($isOwner)
                            <select name="payer_id" required
                                    style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;color:#1e293b;background:white;">
                                @foreach($activeMembers as $m)
                                    <option value="{{ $m->id }}" {{ $m->id === auth()->id() ? 'selected' : '' }}>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <select disabled
                                    style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;color:#64748b;background:#f1f5f9;cursor:not-allowed;">
                                <option value="{{ auth()->id() }}" selected>{{ auth()->user()->name }} (Vous)</option>
                            </select>
                            <input type="hidden" name="payer_id" value="{{ auth()->id() }}">
                        @endif
                    </div>
                    <div style="margin-bottom:1.75rem;">
                        <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Catégorie</label>
                        <select name="category_id"
                                style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;color:#1e293b;background:white;">
                            <option value="">Sans catégorie</option>
                            @foreach($colocation->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;gap:1rem;">
                        <button type="button" onclick="document.getElementById('expense-modal').style.display='none'"
                                style="flex:1;padding:0.75rem;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-weight:600;cursor:pointer;">
                            Annuler
                        </button>
                        <button type="submit"
                                style="flex:2;padding:0.75rem;background:#4f46e5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                            <i class="fas fa-plus"></i> Ajouter la dépense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Expense Modal --}}
    @if($colocation->status === 'active')
        <div id="edit-expense-modal"
             style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;
                    align-items:center;justify-content:center;backdrop-filter:blur(4px);">
            <div style="background:white;border-radius:20px;padding:2.5rem;width:100%;max-width:500px;
                        box-shadow:0 25px 50px rgba(0,0,0,0.2);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.75rem;">
                    <h3 style="font-family:'Outfit',sans-serif;font-size:1.3rem;font-weight:700;color:#1e293b;">
                        <i class="fas fa-edit text-indigo-500 mr-2"></i>Modifier la dépense
                    </h3>
                    <button onclick="document.getElementById('edit-expense-modal').style.display='none'"
                            style="background:#f1f5f9;border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;
                                   font-size:1rem;color:#64748b;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="edit-expense-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Titre de la dépense</label>
                        <input type="text" name="title" id="edit-title" required 
                               style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                        <div>
                            <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Montant (€)</label>
                            <input type="number" name="amount" id="edit-amount" required min="0.01" step="0.01" 
                                   style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Date</label>
                            <input type="date" name="date" id="edit-date" required 
                                   style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                    </div>
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Payeur</label>
                        @if($isOwner)
                            <select name="payer_id" id="edit-payer" required
                                    style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;color:#1e293b;background:white;">
                                @foreach($activeMembers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <select id="edit-payer-disabled" disabled
                                    style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;color:#64748b;background:#f1f5f9;cursor:not-allowed;">
                                <option value="{{ auth()->id() }}" selected>{{ auth()->user()->name }} (Vous)</option>
                            </select>
                            <input type="hidden" name="payer_id" id="edit-payer" value="{{ auth()->id() }}">
                        @endif
                    </div>
                    <div style="margin-bottom:1.75rem;">
                        <label style="display:block;font-size:0.83rem;font-weight:600;color:#374151;margin-bottom:0.4rem;">Catégorie</label>
                        <select name="category_id" id="edit-category"
                                style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.9rem;outline:none;box-sizing:border-box;color:#1e293b;background:white;">
                            <option value="">Sans catégorie</option>
                            @foreach($colocation->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;gap:1rem;">
                        <button type="button" onclick="document.getElementById('edit-expense-modal').style.display='none'"
                                style="flex:1;padding:0.75rem;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-weight:600;cursor:pointer;">
                            Annuler
                        </button>
                        <button type="submit"
                                style="flex:2;padding:0.75rem;background:#4f46e5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            function openEditModal(id, title, amount, date, payerId, categoryId) {
                document.getElementById('edit-expense-form').action = `/colocations/{{ $colocation->id }}/expenses/${id}`;
                document.getElementById('edit-title').value = title;
                document.getElementById('edit-amount').value = amount;
                document.getElementById('edit-date').value = date;
                document.getElementById('edit-payer').value = payerId;
                document.getElementById('edit-category').value = categoryId || '';
                document.getElementById('edit-expense-modal').style.display = 'flex';
            }
        </script>
    @endif

</x-app-layout>
