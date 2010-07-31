<?php

return array(
	'worker' => array(
		'default' => array(
			'driver' => 'pecl',
			'servers' => array(
//				'127.0.0.1' => array('127.0.0.1', 4730),
			),
			'functions' => array(
//				'Task_Reverse' => array(
//					'callback' => array('Task_Reverse', 'work'),
//					'timeout' => 3600,
//				),
			),
		),
	),
	'client' => array(
		'default' => array(
			'driver' => 'pecl',
			'servers' => array(
//				array('127.0.0.1', 4730),
			),
		),
	),
);