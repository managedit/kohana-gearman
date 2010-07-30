<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gearman Queue Manager
 *
 * @package    Gearman
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */

class Task_Reverse extends Gearman_Task {
	
	// Used to override the default, generated, function name.
	public function function_name()
	{
		return 'reverse';
	}

	public function _work()
	{
		try
		{
			$workload_size = strlen($this->workload());

			// Validate the supplied workload.
			if ($workload_size > 10)
				throw new Exception('String to be reversed may not exceed 10 characters in length');

			// Lets pretend were doing a LOT of work.
			$x = 0;

			while ($x < $workload_size)
			{
				$this->send_status($x, $workload_size);

				$x++;

				sleep(1);
			}

			// Return the result to the client.
			$this->send_complete(strrev($this->workload()));
		}
		catch (Exception $e)
		{
			// We can catch exceptions here and provide a better message to the client.
			// By default, $e->__toString(); is sent for all un-caught exceptions.
			$this->send_warning($e->getMessage());
			$this->send_fail();

			// NOTE: send_exception should be used above, but its broken in pre 0.13 libgearman builds.
		}
	}

	protected function on_success($result)
	{
		echo "Task_Reverse::on_success ($result)\n";
	}

	protected function on_exception($result)
	{
		echo "Task_Reverse::on_exception ($result)\n";
	}

	protected $warning_text = NULL;

	protected function on_warning($result)
	{
		echo "Task_Reverse::on_warning\n";
		$this->warning_text = $result;
	}

	protected function on_fail()
	{
		echo "Task_Reverse::on_fail\n";
		throw new Exception('Work Failed: '.$this->warning_text);
	}

	protected function on_status($numerator, $denominator)
	{
		echo "Task_Reverse::on_status ($numerator/$denominator)\n";
	}

	protected function on_data($result)
	{
		echo "Task_Reverse::on_data ($result)\n";
	}
}