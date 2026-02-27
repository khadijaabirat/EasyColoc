<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>EasyColoc – Gestion de Colocation</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { background: #f8fafc; font-family: 'Inter', sans-serif; }
            .auth-wrapper { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }
            .auth-brand {
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 60%, #a855f7 100%);
                display: flex; flex-direction: column; justify-content: center;
                padding: 3rem; color: white; position: relative; overflow: hidden;
            }
            .auth-brand::before {
                content: ''; position: absolute; top: -80px; right: -80px;
                width: 300px; height: 300px; border-radius: 50%;
                background: rgba(255,255,255,0.08);
            }
            .auth-brand::after {
                content: ''; position: absolute; bottom: -100px; left: -60px;
                width: 350px; height: 350px; border-radius: 50%;
                background: rgba(255,255,255,0.06);
            }
            .auth-form-panel {
                display: flex; align-items: center; justify-content: center;
                padding: 3rem; background: #fff;
            }
            .auth-card { width: 100%; max-width: 420px; }
            .auth-input {
                width: 100%; padding: 0.75rem 1rem;
                border: 1.5px solid #e2e8f0; border-radius: 10px;
                font-size: 0.95rem; transition: all 0.2s;
                outline: none; color: #1e293b;
            }
            .auth-input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.12); }
            .auth-label { font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; display: block; }
            .auth-btn {
                width: 100%; padding: 0.875rem; background: #4f46e5; color: #fff;
                border: none; border-radius: 10px; font-size: 1rem; font-weight: 600;
                cursor: pointer; transition: all 0.2s; letter-spacing: 0.01em;
            }
            .auth-btn:hover { background: #4338ca; box-shadow: 0 4px 14px rgba(79,70,229,0.4); }
            .feature-tag {
                display: inline-flex; align-items: center; gap: 0.5rem;
                background: rgba(255,255,255,0.15); padding: 0.5rem 1rem;
                border-radius: 9999px; font-size: 0.85rem; margin: 0.25rem;
            }
            @media (max-width: 768px) {
                .auth-wrapper { grid-template-columns: 1fr; }
                .auth-brand { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="auth-wrapper">
            <!-- Brand Panel -->
            <div class="auth-brand">
                <div style="position: relative; z-index: 1;">
                    <div style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                        <i class="fas fa-house-chimney mr-2 opacity-90"></i>EasyColoc
                    </div>
                    <p style="font-size: 1.15rem; opacity: 0.85; margin-bottom: 2.5rem; max-width: 340px; line-height: 1.6;">
                        La plateforme intelligente pour gérer votre colocation sans prise de tête.
                    </p>
                    <div style="margin-bottom: 2rem;">
                        <div class="feature-tag"><i class="fas fa-receipt"></i> Suivi des dépenses partagées</div>
                        <div class="feature-tag"><i class="fas fa-users"></i> Gestion des membres</div>
                        <div class="feature-tag"><i class="fas fa-scale-balanced"></i> Calcul automatique des soldes</div>
                        <div class="feature-tag"><i class="fas fa-star"></i> Système de réputation</div>
                    </div>
                    <div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1.5rem;">
                        <div style="display: flex; gap: 2rem;">
                            <div>
                                <div style="font-size: 1.75rem; font-weight: 700; font-family: 'Outfit', sans-serif;">100%</div>
                                <div style="font-size: 0.8rem; opacity: 0.7;">Automatisé</div>
                            </div>
                            <div>
                                <div style="font-size: 1.75rem; font-weight: 700; font-family: 'Outfit', sans-serif;">0€</div>
                                <div style="font-size: 0.8rem; opacity: 0.7;">Gratuit</div>
                            </div>
                            <div>
                                <div style="font-size: 1.75rem; font-weight: 700; font-family: 'Outfit', sans-serif;">∞</div>
                                <div style="font-size: 0.8rem; opacity: 0.7;">Colocations</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Panel -->
            <div class="auth-form-panel">
                <div class="auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
