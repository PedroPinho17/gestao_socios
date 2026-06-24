<!DOCTYPE html>
<html lang="pt">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 0; padding: 0; }
        html, body { margin: 0; padding: 0; }
        .page {
            page-break-after: always;
            overflow: hidden;
        }
        .page:last-child { page-break-after: auto; }
        img {
            display: block;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
@foreach ($pages as $index => $png)
    <div class="page">
        <img
            src="data:image/png;base64,{{ base64_encode($png) }}"
            width="{{ $widthPt }}"
            height="{{ $heightPt }}"
            style="width: {{ $widthPt }}pt; height: {{ $heightPt }}pt;"
            alt="Página {{ $index + 1 }}"
        >
    </div>
@endforeach
</body>
</html>
