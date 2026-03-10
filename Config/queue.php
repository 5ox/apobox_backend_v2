<?php
$config['Queue'] = [
	// seconds to sleep() when no executable job is found
	'sleeptime' => 20,

	// probability in percent of a old job cleanup happening
	'gcprob' => 10,

	// time (in seconds) after which a job is requeued if the worker doesn't report back
	'defaultworkertimeout' => 120,

	// number of retries if a job fails or times out.
	'defaultworkerretries' => 15,

	// seconds of running time after which the worker will terminate (0 = unlimited)
	'workermaxruntime' => 900, // 15 minutes

	// minimum time (in seconds) which a task remains in the database before being cleaned up.
	'cleanuptimeout' => 2000,

	// instruct a Workerprocess quit when there are no more tasks for it to execute (true = exit, false = keep running)
	'exitwhennothingtodo' => false,

	// pid file path directory (by default goes to the app/tmp/queue folder)
	'pidfilepath' => TMP . 'queue' . DS,

	// determine whether logging is enabled
	'log' => false,

	// set to false to disable (tmp = file in TMP dir)
	'notify' => 'tmp',
];
