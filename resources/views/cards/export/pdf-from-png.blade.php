<!DOCTYPE html>
<html lang="pt">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 0; padding: 0; }
        html, body {
            margin: 0;
            padding: 0;
            width: {{ $widthPt }}pt;
            height: {{ $heightPt }}pt;
        }
        img {
            display: block;
            width: {{ $widthPt }}pt;
            height: {{ $heightPt }}pt;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <img src="{{ $imageUri }}" alt="">
</body>
</html>
