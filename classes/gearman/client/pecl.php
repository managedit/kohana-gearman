<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gearman Queue Manager
 *
 * @package    Gearman
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */

class Gearman_Client_Pecl extends Gearman_Client {
	protected $client;

	protected $jobs = array();
	protected $results = array();

	protected function __construct(array $config)
	{
		parent::__construct($config);

		$this->client = new GearmanClient();

		foreach ($this->config['servers'] as $server)
		{
			$this->client->addServer($server[0], $server[1]);
		}

		$this->client->setCompleteCallback(array($this, 'handle_success_callback'));
		$this->client->setWarningCallback(array($this, 'handle_warning_callback'));
		$this->client->setFailCallback(array($this, 'handle_fail_callback'));
		$this->client->setExceptionCallback(array($this, 'handle_exception_callback'));
		$this->client->setStatusCallback(array($this, 'handle_status_callback'));
		$this->client->setDataCallback(array($this, 'handle_data_callback')); // TODO: Should this be setWorkloadCallback()?

	}

	public function run_job(Gearman_Job $job, $priority = Gearman::PRIORITY_NORMAL)
	{
		do
		{
			$result = NULL;
			
			switch ($priority)
			{
				case Gearman::PRIORITY_LOW:
					$result = $this->client->doLow($job->function_name(), $job->workload());
					break;
				case Gearman::PRIORITY_NORMAL:
					$result = $this->client->do($job->function_name(), $job->workload());
					break;
				case Gearman::PRIORITY_HIGH:
					$result = $this->client->doHigh($job->function_name(), $job->workload());
					break;
				default:
					throw new Gearman_Client_Exception('Invalid priority specified');
			}
			
			switch ($this->client->returnCode())
			{
				case GEARMAN_SUCCESS:
					$this->handle_success($job, $result);
					break;
				case GEARMAN_WORK_WARNING:
					$this->handle_warning($job, $result);
					break;
				case GEARMAN_WORK_FAIL:
					$this->handle_fail($job, $result);
					break;
				case GEARMAN_WORK_EXCEPTION:
					$this->handle_exception($job, $result);
					break;
				case GEARMAN_WORK_STATUS:
					$this->handle_status($job, $this->client->doStatus());
					break;
				case GEARMAN_WORK_DATA:
					$this->handle_data($job, $result);
					break;
			}
		}
		while($this->client->returnCode() != GEARMAN_SUCCESS
			AND $this->client->returnCode() != GEARMAN_WORK_FAIL);

		return $result;
	}

	public function run_job_bg(Gearman_Job $job, $priority = Gearman::PRIORITY_NORMAL)
	{
		$job_handle = NULL;

		switch ($priority)
		{
			case Gearman::PRIORITY_LOW:
				$job_handle = $this->client->doLowBackground($job->function_name(), $job->workload());
				break;
			case Gearman::PRIORITY_NORMAL:
				$job_handle = $this->client->doBackground($job->function_name(), $job->workload());
				break;
			case Gearman::PRIORITY_HIGH:
				$job_handle = $this->client->doHighBackground($job->function_name(), $job->workload());
				break;
			default:
				throw new Gearman_Client_Exception('Invalid priority specified');
		}

		switch ($this->client->returnCode())
		{
			case GEARMAN_SUCCESS:
				return $job_handle;
				break;
			default:
				throw new Gearman_Client_Exception('Unknown error');
		}
		
	}

	public function add_job(Gearman_Job $job, $priority = Gearman::PRIORITY_NORMAL)
	{
		$unique = count($this->jobs);

		switch ($priority)
		{
			case Gearman::PRIORITY_LOW:
				$result = $this->client->addTaskLow($job->function_name(), $job->workload(), NULL, $unique);
				break;
			case Gearman::PRIORITY_NORMAL:
				$result = $this->client->addTask($job->function_name(), $job->workload(), NULL, $unique);
				break;
			case Gearman::PRIORITY_HIGH:
				$result = $this->client->addTaskHigh($job->function_name(), $job->workload(), NULL, $unique);
				break;
			default:
				throw new Gearman_Client_Exception('Invalid priority specified');
		}

		$this->jobs[$unique] = $job;
		$this->results[$unique] = '122';

		return $unique;
	}

	public function add_job_bg(Gearman_Job $job, $priority = Gearman::PRIORITY_NORMAL)
	{
		switch ($priority)
		{
			case Gearman::PRIORITY_LOW:
				$this->client->addTaskLowBackground($job->function_name(), $job->workload());
				break;
			case Gearman::PRIORITY_NORMAL:
				$this->client->addTaskBackground($job->function_name(), $job->workload());
				break;
			case Gearman::PRIORITY_HIGH:
				$this->client->addTaskHighBackground($job->function_name(), $job->workload());
				break;
			default:
				throw new Gearman_Client_Exception('Invalid priority specified');
		}
	}

	public function run_jobs()
	{
		$this->client->runTasks();

		$return = $this->results;
		
		// Cleanup
		$this->jobs = array();
		$this->results = array();
		
		return $return;
	}

	public function check_status($job_handle)
	{
		$status = $this->client->jobStatus($job_handle);

		if ($status[0])
		{
			// Job is known
			if ($status[1])
			{
				// Job is still running - return array(numerator, denomintor)
				return array($status[2], $status[3]);
			}
			else
			{
				// Job has completed
				return array(0,0);
			}
		}
		else
		{
			// Job is not known to the server.
			// TODO: Should this throw an exception?
			// TODO: Check gearman docs for why a job may not be known.
			return NULL;
		}

	}
	
	public function check_completion($job_handle)
	{
		$status = $this->client->jobStatus($job_handle);
		
		if ($status[0])
		{
			// Job is known
			if ($status[1])
			{
				// Job is still running
				return FALSE;
			}
			else
			{
				// Job has completed
				return TRUE;
			}
		}
		else
		{
			// Job is not known to the server.
			// TODO: Should this throw an exception?
			// TODO: Should this return FALSE?
			// TODO: Check gearman docs for why a job may not be known.
			return TRUE;
		}
	}

	public function handle_success_callback(GearmanTask $t)
	{
		$job = $this->jobs[$t->unique()];

		$this->results[$t->unique()] = $t->data();

		try
		{
			$this->handle_success($job, $t->data());
		}
		catch (Exception $e)
		{
			if ( ! isset($this->results[$t->unique()]) OR ! $this->results[$t->unique()] instanceof Gearman_Client_Exception)
			{
				$this->results[$t->unique()] = new Gearman_Client_Exception('Task Failed');
			}

			$this->results[$t->unique()]->exceptions[] = $e;
		}
	}

	public function handle_warning_callback(GearmanTask $t)
	{
		$job = $this->jobs[$t->unique()];

		try
		{
			$this->handle_warning($job, $t->data());
		}
		catch (Exception $e)
		{
			if ( ! isset($this->results[$t->unique()]) OR ! $this->results[$t->unique()] instanceof Gearman_Client_Exception)
			{
				$this->results[$t->unique()] = new Gearman_Client_Exception('Task Failed');
			}

			$this->results[$t->unique()]->exceptions[] = $e;
		}
	}
	public function handle_fail_callback(GearmanTask $t)
	{
		$job = $this->jobs[$t->unique()];

		try
		{
			$this->handle_fail($job);
		}
		catch (Exception $e)
		{
			if ( ! isset($this->results[$t->unique()]) OR ! $this->results[$t->unique()] instanceof Gearman_Client_Exception)
			{
				$this->results[$t->unique()] = new Gearman_Client_Exception('Task Failed');
			}

			$this->results[$t->unique()]->exceptions[] = $e;
		}
	}

	public function handle_exception_callback(GearmanTask $t)
	{
		$job = $this->jobs[$t->unique()];

		try
		{
			$this->handle_exception($job, $t->data());
		}
		catch (Exception $e)
		{
			if ( ! isset($this->results[$t->unique()]) OR ! $this->results[$t->unique()] instanceof Gearman_Client_Exception)
			{
				$this->results[$t->unique()] = new Gearman_Client_Exception('Task Failed');
			}

			$this->results[$t->unique()]->exceptions[] = $e;
		}
	}

	public function handle_status_callback(GearmanTask $t)
	{
		$job = $this->jobs[$t->unique()];

		$status = array($t->taskNumerator(), $t->taskDenominator());

		try
		{
			$this->handle_status($job, $status);
		}
		catch (Exception $e)
		{
			if ( ! isset($this->results[$t->unique()]) OR ! $this->results[$t->unique()] instanceof Gearman_Client_Exception)
			{
				$this->results[$t->unique()] = new Gearman_Client_Exception('Task Failed');
			}

			$this->results[$t->unique()]->exceptions[] = $e;
		}
	}

	public function handle_data_callback(GearmanTask $t)
	{
		$job = $this->jobs[$t->unique()];

		try
		{
			$this->handle_data($job, $t->data());
		}
		catch (Exception $e)
		{
			if ( ! isset($this->results[$t->unique()]) OR ! $this->results[$t->unique()] instanceof Gearman_Client_Exception)
			{
				$this->results[$t->unique()] = new Gearman_Client_Exception('Task Failed');
			}

			$this->results[$t->unique()]->exceptions[] = $e;
		}
	}
}