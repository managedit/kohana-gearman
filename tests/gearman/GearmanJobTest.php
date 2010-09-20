<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Gearman Queue Manager
 *
 * @group      gearman
 * @group      gearman.job
 * @package    Gearman
 * @author     Kiall Mac Innes
 * @copyright  (c) 2010 Kiall Mac Innes
 * @license    http://kohanaframework.org/license
 */
class GearmanJobTest extends PHPUnit_Framework_TestCase {
	/**
	 * Provides test data for test_set_workload()
	 *
	 * @return array
	 */
	function provider_set_workload()
	{
		return array(
			// $value, $result
			array(array('12345')),
			array('12345'),
			array(12345),
		);
	}

	/**
	 * 
	 *
	 * @test
	 * @dataProvider provider_set_workload
	 * @param boolean $input    Input workload
	 * @param boolean $expected Output for File::mime
	 */
	function test_set_workload($input)
	{
		$job = Gearman_Job::factory('Test');
		$this->assertTrue($job->workload($input));
	}
}