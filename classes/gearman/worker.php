<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gearman Queue Manager
 *
 * @package    Gearman
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */

abstract class Gearman_Worker {
	public static $default = 'default';
	public static $instances = array();

	protected $config;

	public static function instance($group = NULL)
	{
		if ($group === NULL)
		{
			$group = Gearman_Worker::$default;
		}

		if (isset(Gearman_Worker::$instances[$group]))
		{
			return Gearman_Worker::$instances[$group];
		}

		$config = Kohana::config('gearman.worker');

		if ( ! array_key_exists($group, $config))
		{
			throw new Gearman_Worker_Exception('Failed to load Gearman_Worker config group: :group', array(':group' => $group));
		}

		$config = $config[$group];

		$gearman_worker_class = 'Gearman_Worker_'.ucfirst($config['driver']);
		Gearman_Worker::$instances[$group] = new $gearman_worker_class($config);

		return Gearman_Worker::$instances[$group];
	}

	protected function __construct(array $config)
	{
		$this->config = $config;
	}

	public function work()
	{
		$this->_work();
	}

	abstract protected function _work();
}