<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 24px; }
        th, td { border: 1px solid #cccccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        h1, h2, p { margin: 0 0 12px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>

    @foreach ($sections as $section)
        <h2>{{ $section['title'] }}</h2>

        @if (!empty($section['description']))
            <p>{{ $section['description'] }}</p>
        @endif

        <table>
            <thead>
                <tr>
                    @foreach ($section['headers'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($section['rows'] as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
