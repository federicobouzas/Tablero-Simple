<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//Tablero::$minutes = $_SERVER["HTTP_HOST"] == "localhost" ? 100 : 10;

class Tablero {

    public static $minutes = 10;
    private static $urlEntregas = "http://admin.gestionmassimple.com/api/deliveryReport/?from_date=##FROM##&to_date=##TO##&delivery_point=all_dp_report";
    private static $urlAgenda = "http://admin.gestionmassimple.com/api/generateAgendaCsv/?from_date=##FROM##&to_date=##TO##&delivery_point=null";
    private static $urlPuntoEntrega = "http://admin.gestionmassimple.com/api/generateScheduleCsv/?date=##DATE##&delivery_point=##POINT##";
    private static $urlAuditoriaCallCenter = "http://admin.gestionmassimple.com/api/createTrackingCsv/?date=##DATE##&app=CallCenter";
    public static $puntos = [
        "Chiclana" => [13, 145, "EXO"],
        "CCD9" => [17, 182, "ENACOM"],
        "Maza" => [33, 363, "EXO"],
        "Balcarce" => [33, 363, "ENACOM"],
        "Sede Comunal 5" => [3, 36, "ENACOM"],
        "Sede Comunal 6" => [3, 36, "ENACOM"],
        "Sede Comunal 12" => [3, 36, "EXO"],
    ];
    public static $feriados = ["2017-10-16"];
    public $agenda = [];
    public $auditoria = [];
    public $eventos = [
        "2017-10-23" => 409,
        "2017-10-30" => 163,
    ];
    public $entregasEventos = [
        "2017-10-24" => 300,
        "2017-10-31" => 120,
    ];
    public $franjasPuntos = [];
    public $entregas = [];
    public $entregasTotales = 0;

    public static function getBoundsDays($pasado, $today = true, $days = 15) {
        if ($pasado) {
            return [strtotime(date("Y-m-d", strtotime('-' . ($days + 1) . ' days'))), strtotime(date("Y-m-d", strtotime($today ? '+0 days' : '-1 days')))];
        }
        return [strtotime(date("Y-m-d", strtotime($today ? '+0 days' : '+1 days'))), strtotime(date("Y-m-d", strtotime('+' . ($days + 1) . ' days')))];
    }

    public static function getDates($pasado, $today = true, $format = "Y-m-d", $days = 15) {
        list($min, $max) = Tablero::getBoundsDays($pasado, $today, $days);
        $fechasCompleto = [];
        for ($i = $min; $i <= $max; $i = $i + 60 * 60 * 24) {
            $fechasCompleto[] = date($format, $i);
        }
        return $fechasCompleto;
    }

    public static function getNoHabiles($pasado, $today = true) {
        list($min, $max) = Tablero::getBoundsDays($pasado, $today);
        $bands = [];
        for ($i = $min; $i <= $max; $i = $i + 60 * 60 * 24) {
            $dayOfWeek = date('w', $i);
            if ($dayOfWeek == 6 || $dayOfWeek == 0 || in_array(date("Y-m-d", $i), self::$feriados) !== false) {
                $bands[] = ["from" => ($i - $min) / 24 / 3600 - 0.5, "to" => ($i - $min) / 24 / 3600 + 0.5, "color" => 'rgba(236, 236, 236, 1)'];
            }
        }
        return $bands;
    }

    public static function fillDates($dates, $pasado, $today) {
        $newDates = [];
        list($min, $max) = Tablero::getBoundsDays($pasado, $today);
        for ($i = $min; $i <= $max; $i = $i + 60 * 60 * 24) {
            $newDates[$i] = isset($dates[$i]) ? $dates[$i] : 0;
        }
        return $newDates;
    }

    public static function fillDatesPuntos($dates, $pasado, $today, $days = 15) {
        $blanco = [];
        foreach (array_keys(self::$puntos) as $punto) {
            $blanco[$punto] = 0;
        }
        $newDates = [];
        list($min, $max) = Tablero::getBoundsDays($pasado, $today, $days);
        for ($i = $min; $i <= $max; $i = $i + 60 * 60 * 24) {
            $newDates[$i] = isset($dates[$i]) ? array_merge($blanco, $dates[$i]) : $blanco;
        }
        return $newDates;
    }

    public static function getChartSeries($series) {
        $chartSeries = [];
        if (is_array(reset($series))) {
            foreach ($series as $name => $data) {
                $chartSeries[] = ["name" => $name, "data" => array_values($data)];
            }
        } else {
            $chartSeries[] = ["name" => "Total", "data" => array_values($series)];
        }
        //"marker" => ["fillColor" => "white", "lineWidth" => 2, "lineColor" => "red"],
        return $chartSeries;
    }

    public function getData($file, $url, $vars) {
        file_put_contents("time/" . $file, time());
        $parsedUrl = str_replace(" ", "%20", str_replace(array_keys($vars), array_values($vars), $url));
        $startTime = time();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $parsedUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI1OWIxNTk0MDU0NTZlMjAwMmU5NGM3NWEiLCJ1c2VybmFtZSI6ImZlZGVib3V6YXMiLCJmaXJzdF9uYW1lIjoiRmVkZXJpY28iLCJsYXN0X25hbWUiOiJCb3V6YXMiLCJlbWFpbCI6ImZlZGVyaWNvYm91emFzQGdtYWlsLmNvbSIsInJvbGUiOlsiYWRtaW4iLCJjYWxsIl0sIndvcmtzcGFjZSI6IkJhbGNhcmNlIiwiaWF0IjoxNTA3NzU5MzA2fQ.CDkCKhnCEDySjJz0f2_etgn94ivVSWgHw-uPPgXvXG8'));
        $output = curl_exec($ch);
        curl_close($ch);
        $endTime = time();
        file_put_contents("data/" . $file . ".csv", $output);
        file_put_contents("logs/curls", "TIME: " . date("Y-m-d H:i:s", $startTime) . " | DURACION: " . ($endTime - $startTime) . " | FILE: " . $file . " | URL: " . $parsedUrl . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function getDataFile($file, $url, $vars) {
        if (file_exists("time/" . $file)) {
            $time = file_get_contents("time/" . $file);
            if ($time === false) {
                $this->getData($file, self::${$url}, $vars);
            } else {
                $interval = abs(time() - $time);
                $minutes = round($interval / 60);
                if ($minutes > self::$minutes) {
                    $this->getData($file, self::${$url}, $vars);
                }
            }
        } else {
            $this->getData($file, self::${$url}, $vars);
        }
    }

    public function agenda() {
        if (count($this->agenda)) {
            return;
        }
        $this->getDataFile("agenda", "urlAgenda", ["##FROM##" => date("Y-m-d", strtotime('+1 days')), "##TO##" => date("Y-m-d", strtotime('+14 days'))]);
        if (($gestor = fopen("data/agenda.csv", "r")) !== FALSE) {
            $fila = 1;
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                if ($fila++ == 1 || !isset($datos[1])) {
                    continue;
                }
                $punto = $datos[0];
                $array_fecha = explode("-", $datos[1]);
                $time = strtotime($array_fecha[2] . "-" . $array_fecha[1] . "-" . $array_fecha[0]);
                if (!isset($this->agenda[$time][$punto])) {
                    $this->agenda[$time][$punto] = 0;
                }
                $this->agenda[$time][$punto] ++;
            }
            fclose($gestor);
        }
    }

    public function entregas() {
        if (count($this->entregas)) {
            return;
        }
        $this->getDataFile("entregas", "urlEntregas", ["##FROM##" => date("Y-m-d", strtotime('-15 days')), "##TO##" => date("Y-m-d", strtotime('+1 days'))]);
        if (($gestor = fopen("data/entregas.csv", "r")) !== FALSE) {
            $fila = 1;
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                if ($fila++ == 1 || !isset($datos[7]) || strstr($datos[7], "Secretaría Tercera Edad") !== FALSE) {
                    continue;
                }
                $arrayFecha = explode("-", $datos[8]);
                $arrayFecha1 = explode("/", $arrayFecha[0]);
                $time = strtotime($arrayFecha1[2] . "-" . $arrayFecha1[1] . "-" . $arrayFecha1[0] . " " . $arrayFecha[1]);
                $date = date("d/m", $time);
                if (!isset($this->entregas[$date])) {
                    $this->entregas[$date] = 0;
                }
                $this->entregas[$date] ++;
            }
            fclose($gestor);
        }
    }

    public function getEntregasCategories() {
        $this->entregas();
        return json_encode(array_keys($this->entregas));
    }

    public function getEntregas() {
        $this->entregas();
        return json_encode(Tablero::getChartSeries($this->entregas));
    }

    public function getEntregasTotales() {
        if ($this->entregasTotales > 0 && file_exists("data/entregas_totales")) {
            $this->entregas();
            $this->entregasTotales = (int) file_get_contents("data/entregas_totales");
            if (isset($this->entregas[date("d/m")])) {
                $this->entregasTotales += (int) $this->entregas[date("d/m")];
            }
        }
        return $this->entregasTotales;
    }

    private static function getTotalAgendamientosDiariosPosibles() {
        $total = 0;
        foreach (self::$puntos as $punto) {
            $total += $punto[1];
        }
        return $total;
    }

    private static function nextWorkingDay() {
        $tmpDate = date("Y-m-d");
        $i = 1;
        $date = date('Y-m-d', strtotime($tmpDate . ' +' . $i . ' Weekday'));
        while (in_array($date, self::$feriados)) {
            $i++;
            $date = date('Y-m-d', strtotime($tmpDate . ' +' . $i . ' Weekday'));
        }
        return $date;
    }

    public function getTotalAgendamientoPunto($punto) {
        if (empty($this->agenda)) {
            $this->agenda();
        }
        if (empty($this->agenda[strtotime(self::nextWorkingDay())])) {
            return 0;
        }
        return round($this->agenda[strtotime(self::nextWorkingDay())][$punto] / self::$puntos[$punto][1] * 100);
    }

    public function getAgendaPuntos($days = 15) {
        if (empty($this->agenda)) {
            $this->agenda();
        }
        $agendaPorcentajes = [];
        foreach (Tablero::fillDatesPuntos($this->agenda, false, false, $days) as $agenda) {
            foreach ($agenda as $punto => $agendaPunto) {
                $agendaPorcentajes[$punto][] = round($agendaPunto / self::$puntos[$punto][1] * 100);
            }
        }
        return json_encode(Tablero::getChartSeries($agendaPorcentajes));
    }

    public function getAgenda() {
        if (empty($this->agenda)) {
            $this->agenda();
        }
        $agendaPorcentajes = [];
        $total = self::getTotalAgendamientosDiariosPosibles();
        foreach (Tablero::fillDatesPuntos($this->agenda, false, false) as $agenda) {
            $agendaPorcentajes[] = round(array_sum($agenda) / $total * 100);
        }
        return json_encode(Tablero::getChartSeries($agendaPorcentajes));
    }

    public function auditoria() {
        $today = date("Y-m-d");
        $this->getDataFile("auditoria_hoy", "urlAuditoriaCallCenter", ["##DATE##" => $today]);
        if (file_exists("data/auditoria_hoy.csv")) {
            copy("data/auditoria_hoy.csv", "data/auditoria/" . $today . ".csv");
            unlink("data/auditoria_hoy.csv");
        }
        foreach (self::getDates(true) as $fecha) {
            $this->auditoria[$fecha] = isset($this->eventos[$fecha]) && is_numeric($this->eventos[$fecha]) ? $this->eventos[$fecha] : 0;
            $file = "data/auditoria/" . $fecha . ".csv";
            if (!file_exists($file)) {
                $this->getData("auditoria/" . $fecha, self::$urlAuditoriaCallCenter, ["##DATE##" => $fecha]);
            }
            if (($gestor = fopen($file, "r")) !== FALSE) {
                $fila = 1;
                while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                    if ($fila++ == 1) {
                        continue;
                    }
                    if ($datos[1] == "Asignacion de turno" && strstr($datos[2], "Call") !== false) {
                        $this->auditoria[$fecha] ++;
                    }
                }
                fclose($gestor);
            }
        }
        ksort($this->auditoria);
    }

    public function getAuditoria() {
        $this->auditoria();
        return json_encode(Tablero::getChartSeries($this->auditoria));
    }

    private function setFranjasHorariosPunto($punto) {
        $this->franjasPuntos[$punto] = [];
        if (($gestor = fopen("data/puntos/" . $punto . ".csv", "r")) !== FALSE) {
            $fila = 1;
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                if ($fila++ == 1 || !isset($datos[3])) {
                    continue;
                }
                $number = (int) str_replace(".", ",", $datos[3]);
                $this->franjasPuntos[$punto][] = ["y" => $number, "color" => ($number >= 100 ? "#6D9E47" : ($number >= 75 ? "#FDC832" : "#C64238"))];
            }
            fclose($gestor);
        }
    }

    public function getFranjasPuntos() {
        foreach (array_keys(self::$puntos) as $punto) {
            self::getDataFile($punto, "urlPuntoEntrega", ["##DATE##" => self::nextWorkingDay(), "##POINT##" => $punto]);
            if (file_exists("data/" . $punto . ".csv")) {
                copy("data/" . $punto . ".csv", "data/puntos/" . $punto . ".csv");
                unlink("data/" . $punto . ".csv");
            }
        }
        foreach (array_keys(self::$puntos) as $punto) {
            $this->setFranjasHorariosPunto($punto);
        }
        return $this->franjasPuntos;
    }

}

function d($var, $die = true) {
    var_dump($var);
    if ($die) {
        die;
    }
}
