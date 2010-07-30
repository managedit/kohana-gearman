<?php
class Controller_Demo extends Controller
{

	public function action_client($workload = 'Testing')
	{
		ob_end_flush();

		$client = Gearman_Client::instance('default');
		$task = Gearman_Task::factory('Task_Reverse');
		$task->workload($workload);

		try
		{
			$result = $client->do_task($task);
			var_dump($result);
		}
		catch (Exception $e)
		{
			echo 'Caught Exception: '.$e->getMessage()."\n";
		}
	}

	public function action_worker()
	{
		ob_end_flush();
		
		$worker = Gearman_Worker::instance('default');

		while ($worker->work());
	}
}