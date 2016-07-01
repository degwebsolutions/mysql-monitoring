<?php
require __DIR__ . '/vendor/autoload.php';

$config = [
    'mysqlUser'      => 'root',
    'mysqlPassword'  => 'root',
    'mysqlHost'      => '127.0.0.1',
    'influxHost'     => 'localhost',
    'influxPort'     => 8086,
    'influxUser'     => 'mysqlMetrics',
    'influxPassword' => 'mysqlMetrics',
    'influxDb'       => 'mysqlMetrics',
    'delayInterval'  => 60,
];
$hostName = system('hostname');
$dbh      = new PDO('mysql:host=' . $config['mysqlHost'] . '', $config['mysqlUser'], $config['mysqlPassword']);
$sth      = $dbh->prepare('SHOW STATUS');
$timezone = date_default_timezone_get();

$database = InfluxDB\Client::fromDSN(sprintf('influxdb://' . $config['influxUser'] . ':' . $config['influxPassword'] . '@%s:%s/%s', $config['influxHost'], $config['influxPort'], $config['influxDb']));

$client = $database->getClient();

while (1) {
    $timeStamp = system('date +%s%N');
    $sth->execute();
    $res    = $sth->fetchAll();
    $points = [];
    echo json_encode($res) . "\n";
    foreach ($res as $row) {
        $row['Value'] = trim($row['Value']);

        if ($row['Value'] == 'OFF') {
            $row['Value'] = false;
        } elseif ($row['Value'] == 'ON') {
            $row['Value'] = true;
        } elseif (preg_match('/^\d\.\d*$/', $row['Value'])) {
            $row['Value'] = (float) $row['Value'];
        } elseif (preg_match('/^\d+$/', $row['Value'])) {
            $row['Value'] = (int) $row['Value'];
        }

        $points[] = new InfluxDB\Point(
            $row['Variable_name'], // name of the measurement
            $row['Value'],         // the measurement value
            [
                'host'   => $hostName,
                'region' => $timezone,
            ],         // optional tags
            [],        // optional additional fields
            $timeStamp // Time precision has to be set to seconds!
        );
        $database->writePoints($points);
    }

    sleep($config['delayInterval']);
}
