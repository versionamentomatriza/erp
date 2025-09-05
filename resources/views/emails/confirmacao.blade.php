<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmação de E-mail</title>
</head>
<body>
   <h1>Confirmação de E-mail</h1>
<p>Olá, {{ $user->name }}!</p>
<p>Por favor, confirme seu e-mail clicando no link abaixo:</p>
<p><a href="{{ $confirmationUrl }}">{{ $confirmationUrl }}</a></p>
<p>Se você não solicitou essa alteração, ignore este e-mail.</p>

</body>
</html>
