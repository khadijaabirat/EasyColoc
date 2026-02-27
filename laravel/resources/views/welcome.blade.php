<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyColoc – Simplifiez votre colocation</title>
    <link rel="stylesheet" href="{{ asset('css/premium.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, #eef2ff 0%, #ffffff 100%);
            padding: 2rem;
            text-align: center;
        }
        .hero-content { max-width: 800px; }
        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }
        .hero-title span {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-group { display: flex; gap: 1rem; justify-content: center; }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 5rem;
        }
        .feature-item i { font-size: 2rem; color: #4f46e5; margin-bottom: 1rem; }
        .feature-item h3 { font-family: 'Outfit', sans-serif; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Gérez votre colocation <span>sans stress</span>.</h1>
            <p class="hero-subtitle">
                EasyColoc vous aide à suivre les dépenses, partager les factures et garder une ambiance saine entre colocataires.
            </p>
            
            <div class="btn-group">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        Accéder au Dashboard <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        Commencer maintenant <i class="fas fa- rocket"></i>
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary" style="padding: 1rem 2rem; font-size: 1.1rem; background: white; color: #4f46e5; border: 2px solid #4f46e5;">
                        Se connecter
                    </a>
                @endauth
            </div>

            <div class="feature-grid">
                <div class="feature-item">
                    <i class="fas fa-receipt"></i>
                    <h3>Dépenses Partagées</h3>
                    <p>Ajoutez vos factures et achats en un clic.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-calculator"></i>
                    <h3>Calcul Automatique</h3>
                    <p>On s'occupe de savoir qui doit quoi à qui.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-star"></i>
                    <h3>Réputation</h3>
                    <p>Valorisez les bons payeurs de la colocation.</p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
