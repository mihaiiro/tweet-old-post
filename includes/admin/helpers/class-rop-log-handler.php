<?php
/**
 *
 * Custom monolog implementation for log handling.
 *
 * @package     rop
 * @subpackage  rop/helpers
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since
 *
 */

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class Rop_Log_Handler, procesor for user logs.
 */
class Rop_Log_Handler extends AbstractProcessingHandler {
	/**
	 * Hold initialization status.
	 *
	 * @var bool Init status.
	 */
	private $initialized = false;
	/**
	 * Option where to save the logs.
	 *
	 * @var string $namespace Option key.
	 */
	private $namespace;
	/**
	 * List of logs.
	 * @var array $current_logs List  of logs.
	 */
	private $current_logs;
	/**
	 * How many logs to save.
	 *
	 * @var int Number of logs.
	 */
	private $limit = 100;

	/**
	 * Rop_Log_Handler constructor.
	 *
	 *
	 * @param string $option_name Option where to save this.
	 * @param int    $level Level of log.
	 * @param bool   $bubble
	 */
	public function __construct( $option_name, $level = Logger::DEBUG, $bubble = true ) {
		$this->namespace = $option_name;

		parent::__construct( $level, $bubble );
	}

	/**
	 * Get all the logs available.
	 *
	 * @return array Logs array.
	 */
	public function get_logs() {
		if ( ! $this->initialized ) {
			$this->initialize();
		}

		return $this->current_logs;
	}

	/*
	 * Initilize logger.
	 */

	private function initialize() {
		$current_logs = get_option( $this->namespace, array() );
		if ( ! is_array( $current_logs ) ) {
			$current_logs = array();
		}
		$this->current_logs = $current_logs;
		$this->initialized  = true;
	}

	/*
	 * Get all logs.
	 */

	/**
	 * Write log handler.
	 *
	 * @param array $record Record written.
	 */
	protected function write( array $record ) {
		if ( ! $this->initialized ) {
			$this->initialize();
		}
		$this->current_logs[] = array(
			'channel' => $record['channel'],
			'level'   => $record['level'],
			'message' => $record['formatted'],
			'time'    => Rop_Scheduler_Model::get_current_time(),
		);
		$this->save_logs();
	}

	/**
	 * Save logs utility.
	 * Check the logs limit is reached, truncate the logs.
	 */
	private function save_logs() {
		if ( count( $this->current_logs ) > $this->limit ) {
			$this->current_logs = array_slice( $this->current_logs, count( $this->current_logs ) - $this->limit, $this->limit );
		}
		update_option( $this->namespace, $this->current_logs, 'no' );
	}
}