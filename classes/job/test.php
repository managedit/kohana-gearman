<?php
class Job_Reverse extends Gearman_Job {

	// Used to override the default, generated, function name.
	public function function_name()
	{
		return 'reverse';
	}

	// Optional on_success callback
	protected function on_success($result)
	{
		echo "Job_Reverse::on_success ($result)\n";
	}

	// Optional on_exception callback (libgearman pre 0.13 has a broken implementation of this)
	protected function on_exception($result)
	{
		echo "Job_Reverse::on_exception ($result)\n";
	}

	protected $warning_text = NULL;

	// Optional on_warning callback
	protected function on_warning($result)
	{
		echo "Job_Reverse::on_warning\n";
		$this->warning_text = $result;
	}

	// Optional on_fail callback
	protected function on_fail()
	{
		echo "Job_Reverse::on_fail\n";
		throw new Exception('Work Failed: '.$this->warning_text);
	}

	// Optional on_status callback
	protected function on_status($numerator, $denominator)
	{
		echo "Job_Reverse::on_status ($numerator/$denominator)\n";
	}

	// Optional on_data callback
	protected function on_data($result)
	{
		echo "Job_Reverse::on_data ($result)\n";
	}
}
