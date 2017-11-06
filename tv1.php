<?php
include_once("Tablero.php");
$tablero = new Tablero();
?>
<html>
    <head>
        <title>Call Center</title>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="120" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <style>
            .highcharts-plot-line-label, 
            .highcharts-label text,
            .highcharts-axis-labels text,
            .highcharts-legend-item text {font-size: 22px !important;}
            .highcharts-container, svg {overflow: initial !important;}
            .panel-body {padding:0 !important; height: 350px; padding-right: 40px !important;}
        </style>
        <script src="http://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
    </head>
    <body>
        <div class="container-fluid" style="margin-top: 20px;">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body" id="agenda"></div>
                </div>
            </div>
            <script>
                Highcharts.chart('agenda', {
                    chart: {type: 'line'},
                    colors: ["#F48C37", "#3FA9A5", "#FDC832", "#4668AD", "#BD4F7E"],
                    exporting: false,
                    credits: false,
                    title: {text: 'Porcentaje de Agendamientos para Próximos 15 Días'},
                    legend: {enabled: false},
                    xAxis: {
                        categories: <?php echo json_encode(Tablero::getDates(false, false, "d/m")); ?>,
                        plotBands: <?php echo json_encode(Tablero::getNoHabiles(false, false)); ?>
                    },
                    yAxis: {labels: {formatter: function () {
                                return this.value + '%';
                            }}, min: 0, max: 100, title: {text: null}},
                    plotOptions: {series: {lineWidth: 5}, line: {marker: {radius: 8, fillColor: "white", lineWidth: 5, lineColor: "#F48C37"}, dataLabels: {enabled: true, format: '{y}%'}}},
                    tooltip: {shared: true, valueSuffix: '%'},
                    series: <?php echo $tablero->getAgenda(); ?>
                });
            </script>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body" id="agenda-deglosado"></div>
                </div>
            </div>
            <script>
                Highcharts.chart('agenda-deglosado', {
                    chart: {type: 'column'},
                    colors: ["#F48C37", "#3FA9A5", "#FDC832", "#4668AD", "#BD4F7E", "#C64238", "#6D9E47", "#888888", "#1F1E23"],
                    exporting: false,
                    credits: false,
                    title: {text: 'Porcentaje de Agendamientos de Puntos de Entrega para Próximos 7 Días'},
                    xAxis: {
                        categories: <?php echo json_encode(Tablero::getDates(false, false, "d/m", 7)); ?>,
                        plotBands: <?php echo json_encode(Tablero::getNoHabiles(false, false)); ?>
                    },
                    yAxis: {labels: {formatter: function () {
                                return this.value + '%';
                            }}, min: 0, max: 100, title: {text: null}},
                    plotOptions: {dataLabels: {enabled: true, format: '{y}%'}},
                    tooltip: {shared: true, valueSuffix: '%'},
                    series: <?php echo $tablero->getAgendaPuntos(7); ?>
                });
            </script>
        </div> 
    </body>
</html>