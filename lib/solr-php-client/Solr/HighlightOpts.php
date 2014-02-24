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

require_once(dirname(__FILE__) . '/SolrOpts.php');
 
/**
 * This class is a class that provides a class interface for Solr's highlighting
 * options.
 */
class Apache_Solr_HighlightOpts extends Apache_Solr_SolrOpts
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision$';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id$';
	
	private static $param_info = array ( 
		'hl' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::BOOLEAN_PARAM, 'default' => false ),
		'hl.fl' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::CSV_PARAM, 'default' => array() ),
		'hl.snippets' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::NUMERIC_PARAM, 'default' => 1 ),
		'hl.fragsize' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::NUMERIC_PARAM, 'default' => 100 ),
		'hl.mergeContiguous' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::BOOLEAN_PARAM, 'default' => false ),
		'hl.requireFieldMatch' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::BOOLEAN_PARAM, 'default' => false ),
		'hl.maxAnalyzedChars' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::NUMERIC_PARAM, 'default' => 512000 ),
		'hl.alternateField' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => '' ),
		'hl.maxAlternateFieldLength' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::NUMERIC_PARAM, 'default' => 0),
		'hl.formatter' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => 'simple' ),
		'hl.simple.pre' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => '<em>' ),
		'hl.simple.post' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => '</em>' ),
		'hl.fragmenter' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => 'gap' ),
		'hl.fragListBuilder' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => ''),
		'hl.fragmentsBuilder' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => ''),
		'hl.useFastVectorHighlighter' => array( 'ovr' => true, 'type' => Apache_Solr_SolrOpts::BOOLEAN_PARAM, 'default' => false),
		'hl.usePhraseHighlighter' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::BOOLEAN_PARAM, 'default' => false),
		'hl.highlightMultiTerm' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::BOOLEAN_PARAM, 'default' => false),
		'hl.regex.slop' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::NUMERIC_PARAM, 'default' => 0.6),
		'hl.regex.pattern' => array( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::STRING_PARAM, 'default' => ''),
		'hl.regex.maxAnalyzedChars' => array ( 'ovr' => false, 'type' => Apache_Solr_SolrOpts::NUMERIC_PARAM, 'default' => 10000)
	);
	
	/**
	 * The only constructor for this is no-argument. All it does is set highlighting to be on.
	 */
	public function __construct() {
		// Set highlighting to on.
		$this->setParameterValue('hl', true);
	}
	
	/**
	 * This function allows you to set which fields will be highlighted.
	 * 
	 * @param array $fields an array that contains the names of the fields you want highlighted
	 */
	public function setHighlightedFields($fields=array()) 
	{
		// If it is a scalar value, wrap it in an array.
		if ( !is_array($fields) ) {
			$fields = array($fields);
		}
		
		// And set the value
		$this->setParameterValue('hl.fl', $fields);
	}
	
	/**
	 * This function adds a field to the list of fields to be highlighted.
	 * 
	 * @param mixed $field_to_add the field(s) that you want added. This can be an array or a single field
	 */
	public function addHighlightedField($field_to_add) 
	{
		// Get the current list of fields.
		$fields = $this->getParameterValue('hl.fl');
		
		// Check to see if it already was set
		if ( is_array($fields) )
		{
			// If the field(s) to add is an array, merge it with the
			// previous array
			if ( is_array($field_to_add) ) 
			{
				$fields = array_merge($fields, $field_to_add);
			} 
			else
			{
				// Otherwise just add it.
				$fields[] = $field_to_add;
			}
		} else {
			// If it wasn't, this is the first field.
			if ( is_array($field_to_add) ) 
			{
				$fields = $field_to_add;
			}
			else
			{
				$fields = array( $field );
			}
		}
		
		// Set it back
		$this->setParameterValue('hl.fl', $fields);
	}
	
	/**
	 * This function removes a field from the list of fields to be highlighted.
	 * 
	 * @param mixed $field_to_add the field(s) that you want added. This can be an array or a single field
	 */
	public function removeHighlightedField($field_to_remove) 
	{
		// Get the current list of fields
		$fields = $this->getParameterValue('hl.fl');
		
		// Check to see if it was already set
		if ( is_array($fields) )
		{
			// If the fields you want to remove are an array
			// calculate the difference
			if ( is_array($field_to_remove) )
			{
				$fields = array_diff($fields, $field_to_remove);
			}
			else
			{
				// Otherwise, just unset that particular index
				unset($fields[array_search($field_to_remove, $fields)]);
			}
		}
		else 
		{
			$fields = self::$param_info['hl.fl']['default'];
		}
		
		// Set it back
		$this->setParameterValue('hl.fl', $fields);
	}
	
	/**
	 * Get an array of the highlighted fields.
	 * @return array the list of highlighted fields
	 */
	public function getHighlightedFields() 
	{
		return $this->getParameterValue('hl.fl');
	}
	
	/**
	 * Set the number of snippets to generate for each field.
	 * 
	 * @param int $num the number of snippets to generate per field
	 */
	public function setNumSnippets($num) 
	{
		$this->setParameterValue('hl.snippets', $num);
	}
	
	/**
	 * Get the number of snippets each field is set to generate.
	 * 
	 * @return the number of snippets
	 */
	public function getNumSnippets() 
	{
		return $this->getParameterValue('hl.snippets');
	}
	
	/**
	 * This function sets the override for a single field.
	 * @param $field the field to override
	 * @param $num the number of snippets for that field to generate
	 */
	public function setNumSnippetsFieldOverride($field, $num) 
	{
		$this->addFieldOverride('hl.snippets', $field, $num);
	}
	
	/**
	 * This function returns the current value for the override for the
	 * given field (if it exists).
	 * 
	 * @param string $field the field you want to know the override of
	 */
	public function getNumSnippetsFieldOverride($field) 
	{
		return $this->getFieldOverride('hl.snippets', $field);
	}
	
	/**
	 * Remove a previously set override.
	 * 
	 * @param string $field the field for which to remove the override
	 */
	public function removeNumSnippetsFieldOverride($field) 
	{
		$this->removeFieldOverride('hl.snippets', $field);
	}
	
	/**
	 * Set the size of the fragments that you want the highlighting to be
	 * split into.  Default is 100. 0 means don't fragment.
	 * @param int $size
	 */
	public function setFragmentSize($size)
	{
		$this->setParameterValue('hl.fragsize', $size);
	}
	
	/**
	 * Get the currently set fragment size.
	 * 
	 * @return int the currently set fragment size
	 */
	public function getFragmentSize()
	{
		return $this->getParameterValue('hl.fragsize');
	}
	
	/**
	 * Set the field override for the fragment size.
	 * @param string $field the field to set the override for
	 * @param int $size the size to set the field to
	 */
	public function setFragmentSizeOverride($field, $size)
	{
		$this->addFieldOverride('hl.fragsize', $field, $size);
	}
	
	/**
	 * Get the field override for a given field's fragment size. 
	 * @param $field the field to get the override for
	 */
	public function getFragmentSizeOverride($field) 
	{
		return $this->getFieldOverride('hl.fragsize', $field);
	}
	
	/**
	 * Remove the fragment size override on a field.
	 * 
	 * @param string $field the field to remove it for
	 */
	public function removeFragmentSizeOverride($field)
	{
		$this->removeFieldOverride('hl.fragsize', $field);
	}
	
	/**
	 * This setting determines whether or not contiguous fragments should be
	 * collapsed into a single fragment.
	 * 
	 * @param boolean $mergeContiguous whether or not do to it
	 */
	public function setMergeContiguous($mergeContiguous) 
	{
		$this->setParameterValue('hl.mergeContiguous', $mergeContiguous);
	}
	
	/**
	 * Get the current setting for merge contiguous.
	 * @return boolean true if it is set to merge contiguous fragments, false if not
	 */
	public function getMergeContiguous() 
	{
		return $this->getParameterValue('hl.mergeContiguous');
	}
	
	/**
	 * Set a field override for merge contiguous.
	 * 
	 * @param string $field the field to set it for
	 * @param boolean $value the value to set it to
	 */
	public function addMergeContiguousFieldOverride($field, $value) 
	{
		$this->addFieldOverride('hl.mergeContiguous', $field, $value);
	}
	
	/**
	 * Get the field override for merge contiguous.
	 * 
	 * @param string $field the field whose value you want
	 * @return boolean the value of the setting for that field
	 */
	public function getMergeContiguousFieldOverride($field) 
	{
		return $this->getFieldOverride('hl.mergeContiguous', $field);
	}
	
	/**
	 * Remove a field override for merge contiguous.
	 * @param string $field the field for which to remove it
	 */
	public function removeMergeContiguousFieldOverride($field) 
	{
		$this->removeFieldOverride('hl.mergeContiguous', $field);
	}
	
	/**
	 * If true, then a field will only be highlighted if the query matched in 
	 * this particular field (normally, terms are highlighted in all requested
	 * fields regardless of which field matched the query). This only takes 
	 * effect if "hl.usePhraseHighlighter" is "true".
	 * 
	 * The default value is "false".
	 * 	 
	 * @param boolean $requireFieldMatch whether or not to require field match
	 */
	public function setRequireFieldMatch($requireFieldMatch) 
	{
		$this->setParameterValue('hl.requireFieldMatch', $requireFieldMatch);		
	}
	
	/**
	 * Get whether or not a field match is required.
	 * 
	 * @return boolean true if field match is required, false if not
	 */
	public function getRequireFieldMatch() 
	{
		return $this->getParameterValue('hl.requireFieldMatch');
	}
	
	/**
	 * How many characters into a document to look for suitable snippets.
	 * 
	 * The default value is "51200".
	 * 
	 * @param int $max the new max
	 */
	public function setMaxAnalyzedChars($max) 
	{
		$this->setParameterValue('hl.maxAnalyzedChars',$max);
	}
	
	/**
	 * Get the max number of characters to analyze.
	 * @return int the max number of characters to analyze
	 */
	public function getMaxAnalyzedChars()
	{
		return $this->getParameterValue('hl.maxAnalyzedChars');
	}
	
	/**
	 * If a snippet cannot be generated, this field will be used instead.
	 * 
	 * The default value is to not have one.
	 * 
	 * @param string $field the field to use as the alternate field
	 */
	public function setAlternateField($field) 
	{
		$this->setParameterValue('hl.alternateField', $field);
	}
	
	/**
	 * Retrieve the field that will be used if a snippet cannot be generated.
	 * 
	 * @return string the field that will be used
	 */
	public function getAlternateField()
	{
		return $this->getParameterValue('hl.alternateField', $field);
	}
	
	/**
	 * Add an override to the alternate field.
	 * 
	 * @param string $field_to_override the field whose value you want to override
	 * @param string $alternate_field the value to set that override to
	 */
	public function addAlternateFieldOverride($field_to_override, $alternate_field)
	{
		$this->addFieldOverride('hl.alternateField', $field_to_override, $alternate_field);
	}
	
	/**
	 * Get the value that the overridden field was overridden to.
	 * 
	 * @param string $overridden_field the field that was overridden
	 * @return string the field that the $overridden_field was set to use as its default summary
	 */
	public function getAlternateFieldOverride($overridden_field)
	{
		return $this->getFieldOverride('hl.alternateField',$overridden_field);
	}
	
	/**
	 * Remove a field override from the alternate field.
	 * 
	 * @param string $overridden_field the field from which you want to remove the override
	 */
	public function removeAlternateFieldOverride($overridden_field)
	{
		$this->removeFieldOverride('hl.alternateField', $overridden_field);
	}
	
	/**
	 * If hl.alternateField is specified, this parameter specifies the maximum 
	 * number of characters of the field to return  Solr1.3. Any value less 
	 * than or equal to 0 means unlimited.
	 * 
	 * The default value is unlimited.
	 * 
	 * @param int $length the length to restrain the alternate field to
	 */
	public function setMaxAlternateFieldLength($length)
	{
		$this->setParameterValue('hl.maxAlternateFieldLength',$length);
	}
	
	/**
	 * Get the value set for the max length of the alternate field.
	 * 
	 * @return int the max length of the alternate field
	 */
	public function getMaxAlternateFieldLength()
	{
		return $this->getParameterValue('hl.maxAlternateFieldLength');
	}
	
	/**
	 * Specify a formatter for the highlight output. Currently the only 
	 * legal value is "simple", which surrounds a highlighted term with 
	 * a customizable pre- and post text snippet. 
	 * 
	 * The default value is "simple".
	 * 
	 * @param unknown_type $formatter
	 */
	public function setFormatter($formatter) 
	{
		$this->setParameterValue('hl.formatter', $formatter);
	}
	
	/**
	 * Get the formatter that is currently set.
	 * 
	 * @return string the current formatter
	 */
	public function getFormatter() 
	{
		return $this->getParameterValue('hl.formatter');
	}
	
	/**
	 * Override what formatter a certain field uses.
	 * @param string $field the field to override
	 * @param string $formatter the formatter for that field to use
	 */
	public function addFormatterFieldOverride($field, $formatter)
	{
		$this->addFieldOverride('hl.formatter', $field, $formatter);
	}
	
	/**
	 * Get the overridden formatter value for a field.
	 * 
	 * @param string $field the field to get the override for
	 */
	public function getFormatterFieldOverride($field)
	{
		return $this->getFieldOverride('hl.formatter',$field);
	}
	
	/**
	 * Remove an override for a fields formatter.
	 * 
	 * @param string $field the field to remove the override from
	 */
	public function removeFormatterFieldOverride($field)
	{
		$this->removeFieldOverride('hl.formatter', $field);
	}
	
	/**
	 * Set the text which appears before a highlighted term when 
	 * using the simple formatter. 
	 * 
	 * @param string $value the string to prepend
	 */
	public function setPreFormat($value)
	{
		$this->setParameterValue('hl.simple.pre',$value);
	}
	
	/**
	 * Get what is prepended to highlighted terms.
	 * 
	 * @return string the value that is prepended to highlighted terms
	 */
	public function getPreFormat()
	{
		return $this->getParameterValue('hl.simple.pre');
	}
	
	/**
	 * Set a field override for the pre-formatting.
	 * 
	 * @param string $field the field to override
	 * @param string $value the value to override it with
	 */
	public function addPreFormatFieldOverride($field, $value)
	{
		$this->addFieldOverride('hl.simple.pre', $field, $value);
	}
	
	/**
	 * Get the field override for pre-formatting for a given field.
	 * @param string $field the field to get the override for
	 */
	public function getPreFormatFieldOverride($field)
	{
		return $this->getFieldOverride('hl.simple.pre', $field);
	}
	
	/**
	 * Remove the field override for pre-formatting for a given field.
	 * 
	 * @param string $field the field to remove it from
	 */
	public function removePreFormatFieldOverride($field)
	{
		$this->removeFieldOverride('hl.simple.pre', $field);
	}
	
	/**
	 * Set the text which appears after a highlighted term when 
	 * using the simple formatter. 
	 * 
	 * @param string $value the string to append
	 */
	public function setPostFormat($value)
	{
		$this->setParameterValue('hl.simple.post',$value);
	}
	
	/**
	 * Get the text which appears after a highlighted term when 
	 * using the simple formatter. 
	 * 
	 * @param string $value the string to append
	 */
	public function getPostFormat()
	{
		return $this->getParameterValue('hl.simple.post');
	}
	
	/**
	 * Add an override for the value that gets appended to a highlighted
	 * term.
	 * 
	 * @param string $field the field to override
	 * @param string $value the value to override it with
	 */
	public function addPostFormatFieldOverride($field, $value)
	{
		$this->addFieldOverride('hl.simple.post', $field, $value);
	}
	
	/**
	 * Get the override for the appended value for a highlighted term.
	 * 
	 * @param string $field the field to look at
	 */
	public function getPostFormatFieldOverride($field)
	{
		return $this->getFieldOverride('hl.simple.post', $field);
	}
	
	/**
	 * Remove the post-highlighting override for a field.
	 * 
	 * @param string $field the field to remove it for.
	 */
	public function removePostFormatFieldOverride($field)
	{
		$this->removeFieldOverride('hl.simple.post', $field);
	}
	
	/**
	 * Specify a text snippet generator for highlighted text. The standard 
	 * fragmenter is gap (which is so called because it creates fixed-sized 
	 * fragments with gaps for multi-valued fields). Another option is regex,
	 * which tries to create fragments that "look like" a certain regular 
	 * expression.
	 *  
	 * The default value is "gap"
	 * 
	 * @param string $value the text snippet generator to use
	 */
	public function setFragmenter($value)
	{
		$this->setParameterValue('hl.fragmenter',$value);
	}
	
	/**
	 * Get the text snippet generator that is being used.
	 * 
	 * @return string the text snippet generator that is being used
	 */
	public function getFragmenter()
	{
		return $this->getParameterValue('hl.fragmenter');
	}
	
	/**
	 * Override which text snippet generator a field uses.
	 * 
	 * @param string $field the field to override
	 * @param string $value the text snippet generator to use
	 */
	public function addFragmenterFieldOverride($field, $value)
	{
		$this->addFieldOverride('hl.fragmenter', $field, $value);
	}
	
	/**
	 * Get the overridden value (if it exists) of which text snippet
	 * generator a field uses.
	 * 
	 * @param string $field the field to check
	 * @return string the text snippet generator it uses
	 */
	public function getFragmenterFieldOverride($field)
	{
		return $this->getFieldOverride('hl.fragmenter', $field);
	}
	
	/**
	 * Remove an overridden text snippet generator value for a
	 * field.
	 *  
	 * @param string $field the field to remove it for
	 */
	public function removeFragmenterFieldOverride($field)
	{
		$this->removeFieldOverride('hl.fragmenter', $field);
	}
	
	/**
	 * Specify the name of SolrFragListBuilder. This parameter makes sense for 
	 * FastVectorHighlighter only.
	 *  
	 * @param string $value the SolrFragListBuilder you wish to use.
	 */
	public function setFragListBuilder($value)
	{
		$this->setParameterValue('hl.fragListBuilder',$value);
	}
	
	/**
	 * Get the name of the SolrFragListBuilder you wish to use.
	 * 
	 * @return string the name of the SolrFragListBuilder being used
	 */
	public function getFragListBuilder()
	{
		return $this->getParameterValue('hl.fragListBuilder');
	}
	
	/**
	 * Specify the name of SolrFragmentsBuilder. This parameter makes sense for
	 * FastVectorHighlighter only. 
	 * 
	 * @param string $value the name of the SolrFragmentsBuilder
	 */
	public function setFragmentsBuilder($value)
	{
		$this->setParameterValue('hl.fragmentsBuilder',$value);
	}
	
	/**
	 * Get the name of the SolrFragmentsBuilder that is being used.
	 * 
	 * @return string the name of the SolrFragmentsBuilder being used
	 */
	public function getFragmentsBuilder()
	{
		return $this->getParameterValue('hl.fragmentsBuilder');
	}
	
	/**
	 * Use FastVectorHighlighter. FastVectorHighlighter requires the field is 
	 * termVectors=on, termPositions=on and termOffsets=on. This parameter 
	 * accepts per-field overrides. 
	 * 
	 * The default value is "false" 
	 * 
	 * @param boolean $value true if you wish to use it, false if not
	 */
	public function setUseFastVectorHighlighter($value)
	{
		$this->setParameterValue('hl.useFastVectorHighlighter',$value);
	}
	
	/**
	 * Get whether or not the FastVectorHighlighter is being used.
	 * 
	 * @return boolean true if it is being used, false if it is not
	 */
	public function getUseFastVectorHighlighter()
	{
		return $this->getParameterValue('hl.useFastVectorHighlighter');
	}
	
	/**
	 * Override whether or not a field is using the FastVectorHighlighter.
	 * 
	 * @param string $field the field that you want to override the value for
	 * @param boolean $value the value you want to override it with
	 */
	public function addUseFastVectorHighlighterFieldOverride($field, $value)
	{
		$this->addFieldOverride('hl.useFastVectorHighlighter', $field, $value);
	}
	
	/**
	 * Get the override for whether or not a field is using the FastVectorHighlighter.
	 * 
	 * @param string $field the field to check
	 * @return boolean whether or not the FastVectorHighlighter is being used for that
	 */
	public function getUseFastVectorHighlighterFieldOverride($field)
	{
		return $this->getFieldOverride('hl.useFastVectorHighlighter', $field);
	}
	
	/**
	 * Remove the override for whether or not a field is using the FastVectorHighlighter.
	 * 
	 * @param string $field the field to remove it for
	 */
	public function removeUseFastVectorHighlighterFieldOverride($field)
	{
		$this->removeFieldOverride('hl.useFastVectorHighlighter', $field);
	}
	
	/**
	 * Use SpanScorer to highlight phrase terms only when they appear within the query 
	 * phrase in the document. 
	 * 
	 * Default is false.
	 * 
	 * @param boolean $value true to do it, false to not
	 */
	public function setUsePhraseHighlighter($value)
	{
		$this->setParameterValue('hl.usePhraseHighlighter',$value);
	}
	
	/**
	 * Get whether or not to only highlight phrases.
	 * 
	 * @return boolean true if it only highlights phrases, false if not
	 */
	public function getUsePhraseHighlighter()
	{
		return $this->getParameterValue('hl.usePhraseHighlighter');
	}
	
	/**
	 * If the SpanScorer is also being used, enables highlighting for 
	 * range/wildcard/fuzzy/prefix queries. 
	 * 
	 * Default is false.
	 * 
	 * @param boolean $value true to use them, false to not
	 */
	public function setHighlightMultiTerm($value)
	{
		$this->setParameterValue('hl.highlightMultiTerm',$value);
	}
	
	/**
	 * Get whether or not highlighting for range/wildcard/fuzzy/prefix
	 * queries is being used.
	 * 
	 * @return boolean true if it is being used, false if not
	 */
	public function getHighlightMultiTerm()
	{
		return $this->getParameterValue('hl.highlightMultiTerm');
	}
	
	/**
	 * Factor by which the regex fragmenter can stray from the ideal fragment 
	 * size (given by hl.fragsize) to accomodate the regular expression. For
	 * instance, a slop of 0.2 with fragsize of 100 should yield fragments 
	 * between 80 and 120 characters in length. It is usually good to provide
	 * a slightly smaller fragsize when using the regex fragmenter.
	 * 
	 * The default value is ".6"
	 * 
	 * @param float $value the value to use
	 */
	public function setRegexSlop($value)
	{
		$this->setParameterValue('hl.regex.slop',$value);
	}
	
	/**
	 * Get the regex slop value (the percentage with which the regex fragmenter can
	 * stray in size.
	 * 
	 * @return float the slop value for the regex fragementer
	 */
	public function getRegexSlop()
	{
		return $this->getParameterValue('hl.regex.slop');
	}
	
	/**
	 * The regular expression for fragmenting. This could be used to extract 
	 * sentences (see example solrconfig.xml)
	 *  
	 * @param string $value the regex to use for fragmenting
	 */
	public function setRegexPattern($value)
	{
		$this->setParameterValue('hl.regex.pattern',$value);
	}
	
	/**
	 * Get the regex used for fragmenting.
	 * 
	 * @return string the regex used for fragmenting
	 */
	public function getRegexPattern()
	{
		return $this->getParameterValue('hl.regex.pattern');
	}
	
	/**
	 * Only analyze this many characters from a field when using the regex 
	 * fragmenter (after which, the fragmenter produces fixed-sized fragments).
	 * Applying a complicated regex to a huge field is expensive.
	 * 
	 * The default value is "10000".
	 * 
	 * @param int $value the max number of characters to analyze with the regex 
	 */
	public function setRegexMaxAnalyzedChars($value)
	{
		$this->setParameterValue('hl.regex.maxAnalyzedChars',$value);
	}
	
	/**
	 * Get the max number of characters to analyze with a regex fragmenter.
	 * 
	 * @return int the max number of characters to analyze with a regex fragmenter
	 */
	public function getRegexMaxAnalyzedChars()
	{
		return $this->getParameterValue('hl.regex.maxAnalyzedChars');
	}
	
	/**
	 * This function should return whether or not the $param fed in accepts field overrides
	 * or not.
	 *
	 * @param string $param this is the parameter you are testing for overrideability
	 * @return boolean true if it overrideable, false if it isn't
	 */
	protected function isOverrideable($param) 
	{
		return self::$param_info[$param]['ovr'];
	}
	
	/**
	 * This function returns the type of parameter that $param is.
	 *
	 * @param string $param the parameter whose type you want
	 * @return string the type the parameter is.
	 */
	protected function getParamType($param)
	{
		return self::$param_info[$param]['type'];
	}

	/**
	 * This function returns the default value of a parameter.
	 *
	 * @param string $param the parameter you want the default value for
	 * @return string the default value of the parameter
	 */
	protected function getDefaultValue($param)
	{
		return self::$param_info[$param]['default'];
	}
}