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

	public function do_task(Gearman_Task $task, $priority = Gearman::PRIORITY_NORMAL)
	{
		do
		{
			$result = NULL;
			
			switch ($priority)
			{
				case Gearman::PRIORITY_LOW:
					$result = $this->client->doLow($task->function_name(), $task->workload());
					break;
				case Gearman::PRIORITY_NORMAL:
					$result = $this->client->do($task->function_name(), $task->workload());
					break;
				case Gearman::PRIORITY_HIGH:
					$result = $this->client->doHigh($task->function_name(), $task->workload());
					break;
				default:
					throw new Gearman_Client_Exception('Invalid priority specified');
			}
			
			switch ($this->client->returnCode())
			{
				case GEARMAN_SUCCESS:
					$this->handle_success($task, $result);
					break;
				case GEARMAN_WORK_WARNING:
					$this->handle_warning($task, $result);
					break;
				case GEARMAN_WORK_FAIL:
					$this->handle_fail($task, $result);
					break;
				case GEARMAN_WORK_EXCEPTION:
					$this->handle_exception($task, $result);
					break;
				case GEARMAN_WORK_STATUS:
					$this->handle_status($task, $this->client->doStatus());
				  break;
				case GEARMAN_WORK_DATA:
					$this->handle_data($task, $result);
				  break;
			}
		}
		while($this->client->returnCode() != GEARMAN_SUCCESS
			AND $this->client->returnCode() != GEARMAN_WORK_FAIL);

		return $result;
	}

	public function do_taskset(Gearman_TaskSet $task)
	{
		throw new Gearman_Client_Exception('TaskSet\'s are currently un-supported');
	}
}