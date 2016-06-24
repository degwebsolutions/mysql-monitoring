<?php
$config = [
    'mysqlUser'      => 'root',
    'mysqlPassword'  => 'root',
    'host'           => '127.0.0.1',
    'influxUser'     => 'mysqlMetrics',
    'influxPassword' => 'mysqlMetrics',
    'influxDb'       => 'mysqlMetrics',
    'delayInterval'  => 60,
];
$hostName = system('hostname');
$dbh      = new PDO('mysql:host=' . $config['host'] . '', $config['mysqlUser'], $config['mysqlPassword']);
$sth      = $dbh->prepare('SHOW STATUS');
$timezone = date_default_timezone_get();

while (1) {
    $nanotime = system('date +%s%N');
    $sth->execute();
    $res = $sth->fetchAll();

    foreach ($res as $row) {
        $row['Value'] = trim($row['Value']);

        if ($row['Value'] == 'OFF') {
            $row['Value'] = 'f';
        } elseif ($row['Value'] == 'ON') {
            $row['Value'] = 't';
        } elseif (!preg_match('/^\d\.?\d*$/', $row['Value'])) {
            $row['Value'] = '\"' . $row['Value'] . '\"';
        }

        $cmd = 'curl -i -u ' . $config['influxUser'] . ':' . $config['influxPassword'] . ' -XPOST http://localhost:8086/write?db=' .
            $config['influxDb'] . ' --data-binary "' . $row['Variable_name'] . ',host=' . $hostName . ',region=' . $timezone .
            ' value=' . (empty($row['Value']) ? '0i' : is_int($row['Value']) ? $row['Value'] . 'i' : $row['Value']) . ' ' . $nanotime . '"';
        echo shell_exec($cmd) . "\n";
    }

    sleep($config['delayInterval']);
}
