<!-- resources/views/imprimir_automatica.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Impressão Automática</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <iframe id="pdf-frame" src="{{ $pdfPath }}"></iframe>
    <script>
        window.onload = function () {
            var iframe = document.getElementById('pdf-frame');
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    </script>
</body>
</html>
