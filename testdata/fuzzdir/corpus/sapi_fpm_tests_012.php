<?php

include "include.inc";

$logfile = dirname(__FILE__).'/php-fpm.log.tmp';
$pidfile = dirname(__FILE__).'/php-fpm.pid';
$port = 9000+PHP_INT_SIZE;

$cfg = <<<EOT
[global]
error_log = $logfile
pid = $pidfile
[unconfined]
listen = 127.0.0.1:$port
ping.path = /ping
ping.response = pong
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
EOT;

$fpm = run_fpm($cfg, $tail);
if (is_resource($fpm)) {
    fpm_display_log($tail, 2);
    try {
		var_dump(strpos(run_request('127.0.0.1', $port), 'pong'));
		echo "IPv4 ok\n";
	} catch (Exception $e) {
		echo "IPv4 error\n";
	}

	$pid=file_get_contents($pidfile);
	if ($pid) {
		exec("kill -USR2 $pid");
	} else {
		die("PID not found\n");
	}
    fpm_display_log($tail, 5);

    try {
		var_dump(strpos(run_request('127.0.0.1', $port), 'pong'));
		echo "IPv4 ok\n";
	} catch (Exception $e) {
		echo "IPv4 error\n";
	}

	proc_terminate($fpm);
    stream_get_contents($tail);
    fclose($tail);
    proc_close($fpm);
}

?>
