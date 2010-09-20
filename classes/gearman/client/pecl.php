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

	protected function __construct(array $config)
	{
		parent::__construct($config);

		$this->client = new GearmanClient();

		foreach ($this->config['servers'] as $server)
		{
			$this->client->addServer($server[0], $server[1]);
		}
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

//	public function do_task_background(Gearman_Task $task, $priority = Gearman::PRIORITY_NORMAL)
//	{
//		do
//		{
//			$job_handle = NULL;
//
//			switch ($priority)
//			{
//				case Gearman::PRIORITY_LOW:
//					$job_handle = $this->client->doLowBackground($task->function_name(), $task->workload());
//					break;
//				case Gearman::PRIORITY_NORMAL:
//					$job_handle = $this->client->doBackground($task->function_name(), $task->workload());
//					break;
//				case Gearman::PRIORITY_HIGH:
//					$job_handle = $this->client->doHighBackground($task->function_name(), $task->workload());
//					break;
//				default:
//					throw new Gearman_Client_Exception('Invalid priority specified');
//			}
//
//			if ($this->client->returnCode() != GEARMAN_SUCCESS)
//			{
//				throw new Gearman_Client_Exception();
//			}
//
//			return $job_handle;
//		}
//		while($this->client->returnCode() != GEARMAN_SUCCESS
//			AND $this->client->returnCode() != GEARMAN_WORK_FAIL);
//
//		return $result;
//	}

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




}