<?php
$tv = false;
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
            .highcharts-container, svg {overflow: initial !important;}
            .panel-body {padding:0 !important; height: 250px; padding-right: 25px !important;}
        </style>
        <script src="http://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
    </head>
    <body>
        <div class="container-fluid">
            <div class="col-sm-6">
                <div class="panel panel-default" style="margin-top: 20px;">
                    <div class="panel-body" id="auditoria"></div>
                </div>
            </div>
            <script>
                Highcharts.chart('auditoria', {
                    chart: {type: 'spline'},
                    colors: ["#3FA9A5", "#FDC832", "#4668AD", "#BD4F7E", "#F48C37"],
                    exporting: false,
                    credits: false,
                    title: {text: 'Cantidad de Agendamientos Diarios del Call Center'},
                    legend: {enabled: false},
                    xAxis: {
                        categories: <?php echo json_encode(Tablero::getDates(true, true, "d/m")); ?>,
                        plotBands: <?php echo json_encode(Tablero::getNoHabiles(true)); ?>
                    },
                    yAxis: {min: 0, max: 1200, title: {text: null}},
                    plotOptions: {
                        spline: {
                            dataLabels: {enabled: true},
                            marker: {radius: 4, fillColor: 'white', lineWidth: 2, lineColor: "#3FA9A5"}
                        }
                    },
                    tooltip: {shared: true},
                    series: <?php echo $tablero->getAuditoria(); ?>
                });
            </script>
            <div class="col-sm-6">
                <div class="panel panel-default" style="margin-top: 20px;">
                    <div class="panel-body" id="agenda"></div>
                </div>
            </div>
            <script>
                Highcharts.chart('agenda', {
                    chart: {type: 'spline'},
                    colors: ["#F48C37", "#3FA9A5", "#FDC832", "#4668AD", "#BD4F7E"],
                    exporting: false,
                    credits: false,
                    title: {text: 'Porcentaje de Agendamientos para Próximos 15 Días'},
                    legend: {enabled: false},
                    xAxis: {
                        categories: <?php echo json_encode(Tablero::getDates(false, false, "d/m")); ?>,
                        plotBands: <?php echo json_encode(Tablero::getNoHabiles(false, false)); ?>
                    },
                    yAxis: {min: 0, max: 100, title: {text: null}, labels: {formatter: function () {
                                return this.value + '%';
                            }}},
                    plotOptions: {
                        spline: {
                            dataLabels: {enabled: true, format: '{y}%'},
                            marker: {radius: 4, fillColor: 'white', lineWidth: 2, lineColor: "#F48C37"}
                        }
                    },
                    tooltip: {shared: true, valueSuffix: '%'},
                    series: <?php echo $tablero->getAgenda(); ?>
                });
            </script>
            <?php foreach ($tablero->getFranjasPuntos() as $punto => $franja): ?>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-body" id="<?php echo $punto; ?>"></div>
                    </div>    
                </div>    
                <script>
                    Highcharts.chart('<?php echo $punto; ?>', {
                        chart: {type: 'column'},
                        exporting: false,
                        credits: false,
                        title: {text: '<?php echo $punto; ?>'},
                        subtitle: {text: '<strong><?php echo Tablero::$puntos[$punto][2]; ?></strong> - Franja Horaria: <strong><?php echo Tablero::$puntos[$punto][0]; ?></strong> - Diario: <strong><?php echo Tablero::$puntos[$punto][1]; ?></strong>'},
                        xAxis: {categories: ['10:00', '10:30', '11:00', '11:30', '12:00', '13:00', '13:30', '14:00', '14:30', '15:00']},
                        yAxis: {min: 0, max: 100, title: {text: null}, plotLines: [{color: '#3FA9A5', 'label': {'text': <?php echo $tablero->getTotalAgendamientoPunto($punto); ?> + '%', 'align': 'right', y: 5, x: 30}, value: <?php echo $tablero->getTotalAgendamientoPunto($punto); ?>, width: '2', zIndex: 2}]},
                        legend: {enabled: false},
                        plotOptions: {column: {pointPadding: 0.2, borderWidth: 0, dataLabels: {enabled: true, format: '{y}%'}}},
                        series: [{name: 'Convocatoria', data: <?php echo json_encode($franja); ?>}]
                    });
                </script>
            <?php endforeach; ?>
        </div> 
    </body>
</html>