# Example Config File

	return array(
		'worker' => array(
			'default' => array(
				'driver' => 'pecl',
				'servers' => array(
					'192.168.0.1' => array('192.168.0.1', 4730),
					'192.168.0.2' => array('192.168.0.2', 4730),
				),
				'functions' => array(
					'Task_Reverse' => array(
						'callback' => array('Task_Reverse', 'work'),
						'timeout' => 3600,
					),
					'Task_Thumbnail' => array(
						'callback' => array('Task_Thumbnail', 'work'),
						'timeout' => 3600,
					),
				),
			),
		),
		'client' => array(
			'default' => array(
				'driver' => 'pecl',
				'servers' => array(
					'192.168.0.1' => array('192.168.0.1', 4730),
					'192.168.0.2' => array('192.168.0.2', 4730),
				),
			),
		),
	);