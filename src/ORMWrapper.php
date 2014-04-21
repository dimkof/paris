<?php

namespace Paris;

use \Idiorm\ORM;

/**
 * Subclass of Idiorm's ORM class that supports
 * returning instances of a specified class rather
 * than raw instances of the ORM class.
 *
 * You shouldn't need to interact with this class
 * directly. It is used internally by the Model base
 * class.
 */
class ORMWrapper extends ORM {

	/**
	 * The wrapped find_one and find_many classes will
	 * return an instance or instances of this class.
	 */
	protected $_class_name;

	/**
	 * Set the name of the class which the wrapped
	 * methods should return instances of.
	 */
	public function set_class_name($class_name) {
		$this->_class_name = $class_name;
	}

	/**
	 * Add a custom filter to the method chain specified on the
	 * model class. This allows custom queries to be added
	 * to models. The filter should take an instance of the
	 * ORM wrapper as its first argument and return an instance
	 * of the ORM wrapper. Any arguments passed to this method
	 * after the name of the filter will be passed to the called
	 * filter function as arguments after the ORM class.
	 */
	public function filter() {
		$args = func_get_args();
		$filter_function = array_shift($args);
		array_unshift($args, $this);
		if (method_exists($this->_class_name, $filter_function)) {
			return call_user_func_array(array($this->_class_name, $filter_function), $args);
		}
	}

	/**
	 * Factory method, return an instance of this
	 * class bound to the supplied table name.
	 *
	 * A repeat of content in parent::for_table, so that
	 * created class is ORMWrapper, not ORM
	 */
	public static function for_table($table_name, $connection_name = parent::DEFAULT_CONNECTION) {
		static::_setup_db($connection_name);
		return new static($table_name, array(), $connection_name);
	}

	/**
	 * Method to create an instance of the model class
	 * associated with this wrapper and populate
	 * it with the supplied Idiorm instance.
	 */
	protected function _create_model_instance($orm) {
		if ($orm === false) {
			return false;
		}
		$model = new $this->_class_name();
		$model->set_orm($orm);
		return $model;
	}

	/**
	 * Wrap Idiorm's find_one method to return
	 * an instance of the class associated with
	 * this wrapper instead of the raw ORM class.
	 */
	public function find_one($id=null) {
		return $this->_create_model_instance(parent::find_one($id));
	}

	/**
	 * Wrap Idiorm's find_many method to return
	 * an array of instances of the class associated
	 * with this wrapper instead of the raw ORM class.
	 */
	public function find_many() {
		$results = parent::find_many();
		foreach($results as $key => $result) {
			$results[$key] = $this->_create_model_instance($result);
		}
		return $results;
	}

	/**
	 * Wrap Idiorm's create method to return an
	 * empty instance of the class associated with
	 * this wrapper instead of the raw ORM class.
	 */
	public function create($data=null) {
		return $this->_create_model_instance(parent::create($data));
	}
}
