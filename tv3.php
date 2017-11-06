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
            .highcharts-axis-labels text {font-size: 22px !important;}
            .highcharts-container, svg {overflow: initial !important;}
            .panel-body {padding:0 !important; height: 225px; padding-right: 50px !important;}
        </style>
        <script src="http://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
    </head>
    <body>
        <div class="container-fluid" style="margin-top: 20px;">
            <?php foreach (array_slice($tablero->getFranjasPuntos(), 4) as $punto => $franja): ?>
                <div class="col-sm-6">
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
                        yAxis: {labels: {formatter: function () {
                                    return this.value + '%';
                                }}, min: 0, max: 100, title: {text: null}, plotLines: [{color: '#3FA9A5', 'label': {'text': <?php echo $tablero->getTotalAgendamientoPunto($punto); ?> + '%', 'align': 'right', y: 5, x: 50}, value: <?php echo $tablero->getTotalAgendamientoPunto($punto); ?>, width: 4, zIndex: 2}]},
                        legend: {enabled: false},
                        plotOptions: {column: {pointPadding: 0.2, borderWidth: 0, dataLabels: {enabled: true, format: '{y}%'}}},
                        series: [{name: 'Convocatoria', data: <?php echo json_encode($franja); ?>}]
                    });
                </script>
            <?php endforeach; ?>
        </div> 
    </body>
</html>