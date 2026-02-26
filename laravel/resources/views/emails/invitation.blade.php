<!DOCTYPE html>
<html>
<head>
    <title>Invitation EasyColoc</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #2d3748;">Salut !</h2>
        <p>Tu as été invité à rejoindre la colocation <strong>{{ $colocation->name }}</strong> sur <strong>EasyColoc</strong>.</p>
        <p>Pour accepter l'invitation et commencer à gérer vos dépenses ensemble, clique sur le bouton ci-dessous :</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('invitations.accept', $token) }}"
               style="background-color: #4a90e2; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Rejoindre la Colocation
            </a>
        </div>

        <p style="font-size: 0.8em; color: #777;">Si tu n'as pas de compte, tu devras d'abord t'inscrire. Si tu as déjà une colocation active, tu ne pourras دوماً rejoindre une autre.</p>
        <hr style="border: 0; border-top: 1px solid #eee;">
        <p style="text-align: center; font-size: 0.7em; color: #aaa;">&copy; {{ date('Y') }} EasyColoc. Tous droits réservés.</p>
    </div>
</body>
</html>
