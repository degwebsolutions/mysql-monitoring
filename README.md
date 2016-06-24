# mysql-monitoring

##Required prerequisites:
* [InfluxDB](https://github.com/influxdata/influxdb)
* [Grafana](https://github.com/grafana/grafana)
* [PHP](http://php.net/)

This MySQL monitoring script is simply started by
```php
php ./mysqlMonitoring.php
```
and collects data from MySQL server at a defined interval. It transmits the data to InfluxDB server which then can be easily formatted and charted by Grafana server. An exemplar Grafana dashboard file is located in Dashboards folder for basic startup monitoring.