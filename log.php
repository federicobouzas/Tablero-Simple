<html>
    <head>
        <title>Log</title>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="120" />
        <style>
            * {
                font-family:monospace;
                font-size: 11px;
            }
        </style>
    </head>
    <body>
        <?php
        $file = file("logs/curls");
        for ($i = max(0, count($file) - 20); $i < count($file); $i++) {
            echo $file[$i] . "<br />";
        }
        ?>
    </body>
</html>


