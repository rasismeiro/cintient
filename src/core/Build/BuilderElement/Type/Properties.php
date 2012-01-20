<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 *
 *  This file is part of Cintient.
 *
 *  Cintient is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Cintient is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Cintient. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * The properties element is responsible for setting up several local
 * (if defined within a target) or global (if defined at the project
 * level) variables for easy reference in the builder script's scope.
 *
 * @package     Build
 * @subpackage  Type
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Type_Properties extends Build_BuilderElement
{
  protected $_text;

  public function __construct()
  {
    parent::__construct();
    $this->_text = null;
  }

	/**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    return new self();
  }

  public function toAnt()
  {
    if (!$this->isActive()) {
      return true;
    }
    if (!$this->getText()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Empty properties text.', __METHOD__);
      return false;
    }
    $xml = new XmlDoc();
    $properties = parse_ini_string($this->getText());
    foreach ($properties as $key => $value) {
      $xml->startElement('property');
      $xml->writeAttribute('name', $key);
      $xml->writeAttribute('value', $value);
      $xml->endElement();
    }
    return $xml->flush();
  }

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
    	array(
    	  'cb' => 'getHtmlInputTextarea',
    		'name' => 'text',
    		'label' => 'Key=value pairs',
    		'value' => $this->getText(),
    		'help' => 'Lines started with semi-colons [;] are comments.',
      ),
    );
    parent::toHtml(array('title' => 'Properties'), $callbacks);
  }

  public function toPhing()
  {
    return $this->toAnt();
  }

  public function toPhp(Array &$context = array())
  {
    if (!$this->isActive()) {
      return '';
    }
    $php = '';
    if (!$this->getText()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Empty properties text.', __METHOD__);
      return false;
    }
    $properties = parse_ini_string($this->getText());
    foreach ($properties as $key => $value) {
      $context['properties'][self::_expandStr($key, $context)] = self::_expandStr($value, $context);
      $php .= <<<EOT
\$GLOBALS['properties'][expandStr('{$key}') . '_{$context['id']}'] = expandStr('{$value}');
EOT;
    }
    return $php;
  }
}