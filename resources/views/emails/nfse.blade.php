<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>NFS-e disponível</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Olá, {{ $data['name'] }}!</h2>

    <p>
        Sua NFS-e n° <strong>{{ $data['number'] }}</strong> foi emitida com sucesso.
    </p>

    @if(!empty($data['link']))
        <p>
            Você também pode visualizar diretamente pelo link:<br>
            <a href="{{ $data['link'] }}">{{ $data['link'] }}</a>
        </p>
    @endif

    <p>
        O arquivo PDF da NFS-e está em anexo neste e-mail.
    </p>

    <br>

    <p>
        Atenciosamente,<br>
        <strong>Equipe Matriza</strong>
    </p>
</body>
</html>
