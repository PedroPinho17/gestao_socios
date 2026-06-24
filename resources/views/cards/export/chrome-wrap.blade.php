<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: {{ \App\Support\MemberCardDimensions::widthPx(300, $withBleed ?? false) }}px;
            height: {{ \App\Support\MemberCardDimensions::heightPx(300, $withBleed ?? false) }}px;
            overflow: hidden;
            background: #fff;
        }
    </style>
</head>
<body>
@php
    $side = $cardSide ?? 'front';
@endphp
@if ($side === 'back')
    @include('cards.templates.verso')
@else
    @php
        $template = $layout['template'] ?? 'classic';
        if (! view()->exists('cards.templates.'.$template)) {
            $template = 'classic';
        }
    @endphp
    @include('cards.templates.'.$template)
@endif
</body>
</html>
