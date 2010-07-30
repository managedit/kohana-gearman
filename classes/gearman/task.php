<?php
/**
 * Gearman Queue Manager
 *
 * @package    Gearman/Samples
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */

abstract class Gearman_Task {
	protected $complete = FALSE;
	protected $success = FALSE;
	protected $warning = FALSE;
	protected $failed = FALSE;
	protected $exception = FALSE;

	protected $job;
	protected $workload = NULL;

	protected $max_retries = 0;
	protected $retry_count = 0;

	public static function factory($class)
	{
		return new $class;
	}
	
	public function __construct()
	{
		
	}

	public function work($job)
	{
		$this->job = $job;
		$this->workload($job->workload());

		try
		{
			$result = $this->_work();
		}
		catch (Exception $e)
		{
			// Unknown excpetions are caught and sent in full.
			$this->send_warning($e);
			$this->send_fail();

			// NOTE:
			// $gmworker->sendException($e) seems to be broken in the current gearman server? (Fixed in libgearman 0.13 i believe)
		}
	}

	public function workload($workload = FALSE)
	{
		if ($workload)
		{
			$this->workload = $workload;
			
			return TRUE;
		}
		else
		{
			return $this->workload;
		}
	}

	public function function_name()
	{
		return strtolower(Kohana::$environment.'_'.get_class($this));
	}


	// Send Returns - PECL Only at the mo :|
	protected function send_complete($content = NULL)
	{
		$this->job->sendComplete($content);

		return $this;
	}

	protected function send_warning($content = NULL)
	{
		$this->job->sendWarning($content);

		return $this;
	}

	protected function send_fail()
	{
		$this->job->sendFail();

		return $this;
	}

	protected function send_exception($content = NULL)
	{
		$this->job->sendException($content);

		return $this;
	}

	protected function send_status($numerator, $denominator)
	{
		$this->job->sendStatus($numerator, $denominator);

		return $this;
	}

	// Handle Returns
	public function handle_success($result)
	{
		$this->complete = TRUE;
		$this->success = TRUE;
		$this->on_success($result);
	}

	protected function on_success($result)
	{

	}

	public function handle_warning($result)
	{
		$this->warning = TRUE;
		$this->on_warning($result);
	}

	protected function on_warning($result)
	{

	}

	public function handle_fail()
	{
		$this->on_fail();
	}

	protected function on_fail()
	{

	}

	public function handle_exception($result)
	{
		$this->exception = TRUE;
		$this->on_exception($result);
	}

	protected function on_exception($result)
	{

	}

	public function handle_status($numerator, $denominator)
	{
		$this->on_status($numerator, $denominator);
	}

	protected function on_status($numerator, $denominator)
	{

	}

	public function handle_data($result)
	{
		$this->on_data($result);
	}

	protected function on_data($result)
	{

	}
}