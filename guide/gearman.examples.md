# Example Client

[!!] This assumes the worker and task from below

	class Controller_Demo extends Controller {
		public function action_reverse($workload = 'Testing')
		{
			ob_end_flush();

			$client = Gearman_Client::instance('default');
			$job = Gearman_Job::factory('Reverse');
			$job->workload($workload);

			try
			{
				$result = $client->run_job($job);
				var_dump($result);
			}
			catch (Exception $e)
			{
				echo 'Caught Exception: '.$e->getMessage()."\n";
			}
		}
	}

# Example Job

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

# Example Worker

	class Controller_Demo extends Controller {
		public function action_worker()
		{
			ob_end_flush();

			$worker = Gearman_Worker::instance('default');

			while ($worker->work());
		}
	}

# Example Worker Task

	class Task_Reverse extends Gearman_Task {
		// Used to override the default, generated, function name.
		public function function_name()
		{
			return 'reverse';
		}

		// The guts of what the worker actually does
		public function _work()
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
	}

