<?php
class Job_Test extends Gearman_Job {
	// Optional on_success callback
	protected function on_success($result)
	{
		echo "Job_Test::on_success ($result)\n";
	}

	// Optional on_exception callback (libgearman pre 0.13 has a broken implementation of this)
	protected function on_exception($result)
	{
		echo "Job_Test::on_exception ($result)\n";
	}

	// Optional on_fail callback
	protected function on_fail()
	{
		echo "Job_Test::on_fail\n";
		throw new Exception('Work Failed: '.$this->warning_text);
	}
}
