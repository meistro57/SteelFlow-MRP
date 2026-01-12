<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #0056b3;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .title { font-size: 18px; color: #0056b3; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none;" width="50%">
                    <span class="title">SteelFlow MRP</span><br>
                    Enterprise Fabrication Management
                </td>
                <td style="border: none;" width="50%" class="text-right">
                    @yield('header_right')
                </td>
            </tr>
        </table>
    </div>

    <main>
        @yield('content')
    </main>

    <div class="footer">
        Generated on {{ date('Y-m-d H:i:s') }} | Page <span class="pagenum"></span>
    </div>
</body>
</html>
