<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: {{ \App\Support\MemberCardDimensions::widthPx() }}px;
            height: {{ \App\Support\MemberCardDimensions::heightPx() }}px;
            overflow: hidden;
            background: #fff;
        }
    </style>
</head>
<body>
@php
    $template = $layout['template'] ?? 'classic';
    if (! view()->exists('cards.templates.'.$template)) {
        $template = 'classic';
    }
@endphp
@include('cards.templates.'.$template)
</body>
</html>
