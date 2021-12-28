<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifiación del sistema</title>
</head>
<body>
    <h2>{{$title}}</h2>
    @foreach($data as $paragraph)
    <p>{!!$paragraph!!}</p>
    @endforeach
</body>
</html>
