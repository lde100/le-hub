<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LE Overlay</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body {
    width:1920px; height:1080px;  /* Full HD — vMix skaliert */
    background:#000000;           /* Schwarz = transparent via Luma-Key */
    overflow:hidden;
    font-family:system-ui, sans-serif;
}
</style>
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>
