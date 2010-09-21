<?php
class Task_Test extends Gearman_Task {
	// Used to override the default, generated, function name.
	public function function_name()
	{
		return 'test';
	}

	// The guts of what the worker actually does
	public function _work()
	{
		$workload_size = strlen($this->workload());

		// Validate the supplied workload.
		if ($workload_size > 10)
			throw new Exception('String to be reversed may not exceed 10 characters in length');

		// Return the result to the client.
		$this->send_complete(strrev($this->workload()));
	}
}
