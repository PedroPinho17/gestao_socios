<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Cartão — {{ $member->nome }}</title>
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; background: #fff; }
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
