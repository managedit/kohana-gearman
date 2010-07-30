<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gearman Queue Manager
 *
 * @package    Gearman
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */

abstract class Gearman_Client {

	public static $default = 'default';
	public static $instances = array();

	protected $config;

	public static function instance($group = NULL)
	{
		if ($group === NULL)
		{
			$group = Gearman_Client::$default;
		}

		if (isset(Gearman_Client::$instances[$group]))
		{
			return Gearman_Client::$instances[$group];
		}

		$config = Kohana::config('gearman.client');

		if ( ! array_key_exists($group, $config))
		{
			throw new Gearman_Client_Exception('Failed to load Gearman_Client config group: :group', array(':group' => $group));
		}

		$config = $config[$group];

		$gearman_client_class = 'Gearman_Client_'.ucfirst($config['driver']);
		Gearman_Client::$instances[$group] = new $gearman_client_class($config);

		return Gearman_Client::$instances[$group];
	}

	protected function __construct(array $config)
	{
		$this->config = $config;
	}

	abstract public function do_task(Gearman_Task $task);

	abstract public function do_taskset(Gearman_TaskSet $task);

	protected function handle_success($task, $result)
	{
		$task->handle_success($result);
	}

	protected function handle_warning($task, $result)
	{
		$task->handle_warning($result);
	}

	protected function handle_fail($task)
	{
		$task->handle_fail();
	}

	protected function handle_exception($task, $result)
	{
		$task->handle_exception($result);
	}

	protected function handle_status($task, $result)
	{
		list($numerator, $denominator) = $result;
		$task->handle_status($numerator, $denominator);
	}

	protected function handle_data($task, $result)
	{
		$task->handle_data($result);
	}
}