<?php
/**
 * Copyright (c) 2010, Thomas Joiner
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of Conduit Internet Technologies, Inc. nor the names of
 *    its contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright Copyright (c) 2010, Thomas Joiner
 * @license New BSD (http://solr-php-client.googlecode.com/svn/trunk/COPYING)
 *
 * @package Apache
 * @subpackage Solr
 * @author Thomas Joiner
 */

/**
 * This class is an abstract class for 
 */
abstract class Apache_Solr_SolrOpts
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision$';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id$';

	/**
	 * Parameter types for this class
	 */
	const BOOLEAN_PARAM = 'bool';
	const CSV_PARAM = 'csv';
	const NUMERIC_PARAM = 'num';
	const STRING_PARAM = 'str';

	/**
	 * The key in the array in which the overrides will be stored.
	 */
	const OVERRIDE_KEY = 'overrides';
	
	/**
	 * A special constant for the value that will be used to indicate
	 * an unset value.
	 *
	 * This would be where it was declared, but due to the fact that PHP doesn't allow you to
	 * instantiate classes in a definition, NO GO!!!
	 *
	 * const UNSET_VALUE = new stdClass();
	 */
	
	/**
	 * The variable that holds all the information about the query parameters.
	 *
	 * @var array
	 */
	private $_param_map = array();
	
	/**
	 * This method will set a parameter's value.  If the value
	 * given is the same as the default, it will just remove value
	 * for the parameter.
	 *
	 * @param string $param the parameter to be set
	 * @param mixed $value the value to set it to
	 */
	protected function setParameterValue($param, $value) 
	{
		$default = $this->getDefaultValue($param);
		
		// Check to see if the value given is the default value for that parameter
		if ( $value == $default ) 
		{
			// If it is, check to see if there are any overridden fields
			if ( count($this->_param_map[$param][self::OVERRIDE_KEY]) == 0 ) 
			{
				// If there aren't, then just unset the whole parameter
				unset($this->_param_map[$param]);
			} 
			elseif ( isset($this->_param_map[$param]) ) 
			{
				// Otherwise, remove the value so that key doesn't exist anymore
				unset($this->_param_map[$param]['value']);
			}
		} 
		else
		{
			// If the value given wasn't the same as the default value, then check to see if
			// the parameter information has already been added
			if ( !isset($this->_param_map[$param]) ) 
			{
				// If it hasn't been, create it
				$this->_param_map[$param] = array( 'value' => $value, 'type' => $this->getParamType($param) );
				// Make sure the current param is overrideable before we create the array to hold the overrides
				if ( $this->isOverrideable($param) )
				{
					$this->_param_map[$param][self::OVERRIDE_KEY] = array();
				}
			}
			else 
			{
				// If it already existed, just change the value.
				$this->_param_map[$param]['value'] = $value;
			}
		}
	}
	
	/**
	 * This function retrieves the value of a parameter.  If the value has not been
	 * set then it will return the default value.
	 *
	 * @return mixed the value of the parameter
	 */
	protected function getParameterValue($param) 
	{
		return ( !isset($this->_param_map[$param]) || !isset($this->_param_map[$param]['value']) ? $this->getDefaultValue($param) : $this->_param_map[$param]['value'] );
	}
	
	/**
	 * This method will add an override to a particular field for the given parameter. This
	 * method will silently not add the override if the parameter cannot be overridden.
	 *
	 * @param string $param the parameter to be set
	 * @param string $field the field to be set
	 * @param mixed $value the value to set it to
	 */
	protected function addFieldOverride($param, $field, $value) 
	{
		$default = $this->getDefaultValue($param);
		
		// Check that the given parameter is overrideable
		if ( $this->isOverrideable($param) ) 
		{
			// We don't want to bother to send the parameter if it just going to be the default,
			// unless the main value has been overridden and we want to put the value for this
			// particular field back to default
			if ( $value != $default || $this->_param_map[$param]['value'] != $default ) 
			{
				// If we are adding the override without setting the main value, then we need to
				// create the structure as well as setting the value
				if ( !isset($this->_param_map[$param]) ) 
				{
					$this->_param_map[$param] = array( 'type' => $this->getParamType($param), self::OVERRIDE_KEY => array( $field => $value ) );
				}
				else
				{
					// Set the value
					$this->_param_map[$param][self::OVERRIDE_KEY][$field] = $value;
				}
			}
		}
	}
	
	/**
	 * This method retrieves the field override for the given parameter/field pair.
	 * 
	 * @param string $param the parameter to be set
	 * @param string $field the field to be set
	 * @return mixed the override for the given field
	 */
	protected function getFieldOverride($param, $field)
	{
		return $this->_param_map[$param][self::OVERRIDE_KEY][$field];
	}

	
	/**
	 * This function removes an override from a field
	 * 
	 * @param string $param the parameter from which the override needs to be removed
	 * @param string $field the field from which to remove the override
	 */
	protected function removeFieldOverride($param, $field) 
	{
		unset($this->_param_map[$param][self::OVERRIDE_KEY][$field]);
	}
	
	/**
	 * This function produces an array of the parameters that this object encapsulates.
	 * The format is such that it can be consummed by http_build_query.
	 *
	 * @return array the parameters that this object needs added to the request.
	 */
	public function getParamArray() {
		$data = array();
		
		// For every parameter in the map
		foreach ( $this->_param_map as $param => $info ) 
		{
			// We make sure that the value is set ( in case all they did was add an override
			// or they set it to the default value )
			if( isset($info['value']) ) {
				switch ( $info['type'] ) 
				{
				case self::CSV_PARAM:
					// If it is a CSV param, implode it using commas as glue
					$data[$param] = implode(',', $info['value']);
					break;
				case self::BOOLEAN_PARAM:
					// If it is boolean, convert to the english words
					$data[$param] = ( $info['value'] === true ? "true" : "false" );
					break;
				default:
					// Otherwise just add it.
					$data[$param] = $info['value'];
					break;
				}
			}
			
			// If this is an overrideable parameter
			if ( isset($info[self::OVERRIDE_KEY]) )
			{
				// For each of the overrides
				foreach ( $info[self::OVERRIDE_KEY] as $field => $value )
				{
					// Add it to the parameters as f.<field name>.<parameter name> as
					// described on http://wiki.apache.org/solr/SimpleFacetParameters#Parameters
					// and http://wiki.apache.org/solr/HighlightingParameters#HowToOverride
					$data["f.$field.$param"] = $value;
				}
			}
		}
		
		// Return the array.
		return $data;
	}
	
	/**
	 * This function should return whether or not the $param fed in accepts field overrides
	 * or not.
	 *
	 * @param string $param this is the parameter you are testing for overrideability
	 * @return boolean true if it overrideable, false if it isn't
	 */
	abstract protected function isOverrideable($param);
	
	/**
	 * This function returns the type of parameter that $param is.
	 *
	 * @param string $param the parameter whose type you want
	 * @return string the type the parameter is.
	 */
	abstract protected function getParamType($param);
	
	/**
	 * This function returns the default value of a parameter.
	 *
	 * @param string $param the parameter you want the default value for
	 * @return string the default value of the parameter
	 */
	abstract protected function getDefaultValue($param);

}