<?php
$tv = isset($_GET["tv"]) && $_GET["tv"] == 1;
include_once("Tablero.php");
$tablero = new Tablero();
$tablero->getEntregasTotales();
?>
<html>
    <head>
        <title>Call Center</title>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="120" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <style>
<?php if ($tv): ?>
                .highcharts-plot-line-label, 
                .highcharts-label text,
                .highcharts-axis-labels text {font-size: 22px !important;}
<?php endif; ?>
            .panel-body {padding:0 !important; height: <?php echo $tv ? 350 : 250; ?>px; padding-right: 25px !important;}
        </style>
        <script src="http://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
    </head>
    <body>
        <div class="container-fluid">
            <div class="col-sm-12">
                <div class="panel panel-default" style="margin-top: 20px;">
                    <div class="panel-body" id="entregas"></div>
                </div>
            </div>
            <script>
                Highcharts.chart('entregas', {
                    chart: {type: 'areaspline'},
                    colors: ["#F48C37", "#3FA9A5", "#FDC832", "#4668AD", "#BD4F7E"],
                    exporting: false,
                    credits: false,
                    title: {text: 'Entregas en los Últimos 15 Días'},
                    legend: {enabled: false},
                    xAxis: {
                        categories: <?php echo $tablero->getEntregasCategories(); ?>
                    },
                    yAxis: {title: {text: null}},
                    plotOptions: {
                        series: {lineWidth: <?php echo $tv ? 5 : 2; ?>},
                        areaspline: {
                            dataLabels: {enabled: true},
                            fillOpacity: 0.2,
                            marker: {
                                radius: <?php echo $tv ? 8 : 4; ?>,
                                fillColor: 'white',
                                lineWidth: <?php echo $tv ? 5 : 2; ?>,
                                lineColor: "#F48C37"
                            }
                        }
                    },
                    tooltip: {shared: true},
                    series: <?php echo $tablero->getEntregas(); ?>
                });
            </script>
        </div> 
    </body>
</html>