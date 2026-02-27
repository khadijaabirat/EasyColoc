<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 40px 20px; text-align: center; color: white; }
        .content { padding: 40px; text-align: center; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; color: #64748b; font-size: 14px; }
        .button { display: inline-block; padding: 14px 32px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 12px; font-weight: bold; margin-top: 25px; transition: background 0.3s; }
        h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        p { color: #475569; line-height: 1.6; font-size: 16px; }
        .coloc-name { color: #4f46e5; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 EasyColoc</h1>
        </div>
        <div class="content">
            <h2>Vous êtes invité !</h2>
            <p>Bonjour,</p>
            <p>On vous a invité à rejoindre la colocation <span class="coloc-name">"{{ $colocation->name }}"</span> sur EasyColoc.</p>
            <p>Rejoignez vos futurs colocataires pour commencer à gérer vos dépenses partagées en toute simplicité.</p>
            <a href="{{ route('invitations.accept', $token) }}" class="button" style="color: white;">Accepter l'invitation</a>
            <p style="margin-top: 30px; font-size: 14px; color: #94a3b8;">
                Si le bouton ne fonctionne pas, copiez ce lien :<br>
                {{ route('invitations.accept', $token) }}
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} EasyColoc. La gestion simplifiée des colocs.
        </div>
    </div>
</body>
</html>
