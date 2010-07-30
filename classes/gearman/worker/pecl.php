<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gearman Queue Manager
 *
 * @package    Gearman
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */
class Gearman_Worker_Pecl extends Gearman_Worker {
	protected $worker;

	protected function __construct(array $config)
	{
		parent::__construct($config);

		$this->worker = new GearmanWorker();
		
		foreach ($this->config['servers'] as $server)
		{
			$this->worker->addServer($server[0], $server[1]);
		}
		var_dump($this->config);
		foreach ($this->config['functions'] as $function)
		{
			$instance = Gearman_Task::factory($function['callback'][0]);
			
			$callback = array($instance, $function['callback'][1]);
			
			$this->worker->addFunction($instance->function_name(), $callback, NULL, $function['timeout']);
		}
	}

	protected function _work()
	{
		while($this->worker->work());
	}
}