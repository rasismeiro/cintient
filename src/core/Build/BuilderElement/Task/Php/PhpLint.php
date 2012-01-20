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
 * The PhpLint task handles lint checking on PHP files.
 *
 * @package     Build
 * @subpackage  Task
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Task_Php_PhpLint extends Build_BuilderElement
{
  protected $_filesets;          // An array of fileset types

  public function __construct()
  {
    parent::__construct();
    $this->_filesets = array();
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    $fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setType(Build_BuilderElement_Type_Fileset::FILE);
    $fileset->setDir('${sourcesDir}');
    $fileset->addInclude('**/*.php');
    $o->setFilesets(array($fileset));
    return $o;
  }

  public function toAnt()
  {
    if (!$this->isActive()) {
      return true;
    }
    $xml = new XmlDoc();
    $xml->startElement('apply');
    if (!$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task php lint.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('executable', 'php');
    if ($this->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($this->getFailOnError()?'true':'false'));
    }
    $xml->startElement('arg');
    $xml->writeAttribute('value', '-l');
    $xml->endElement();
    if ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw($fileset->toAnt());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
      array('cb' => 'getHtmlFailOnError'),
    	array('cb' => 'getFilesets'),
    );
    parent::toHtml(array('title' => 'PhpLint'), $callbacks);
  }

  public function toPhing()
  {
    if (!$this->isActive()) {
      return '';
    }
    $xml = new XmlDoc();
    $xml->startElement('phplint');
    if (!$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task php lint.', __METHOD__);
      return false;
    }
    if ($this->getFailOnError() !== null) {
      $xml->writeAttribute('haltonfailure', ($this->getFailOnError()?'true':'false'));
    }
    if ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw($fileset->toPhing());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  public function toPhp(Array &$context = array())
  {
    if (!$this->isActive()) {
      return true;
    }
    $php = '';
    if (!$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files not set for task PHP lint.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['result']['task'] = 'phplint';
output('Starting...');
";
    if ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      $php .= "
\$callback = function (\$entry, \$baseDir) {
  \$ret = true;
  if (is_file(\$entry)) {
    \$output = array();
    exec(\"" . CINTIENT_PHP_BINARY . " -l \$entry\", \$output, \$ret);
    if (\$ret > 0) {
      output(str_replace(\$baseDir, '', implode(PHP_EOL, \$output)));
      \$ret = false;
    } else {
      output('No syntax errors detected in ' . substr(\$entry, strlen(\$baseDir)) . '.');
      \$ret = true;
    }
  }
  return \$ret;
};
";
      foreach ($filesets as $fileset) {
        $php .= "
" . $fileset->toPhp($context) . "
";
        $php .= "
if (!fileset{$fileset->getId()}_{$context['id']}(\$callback) && {$this->getFailOnError()}) {
  output('Failed.');
  \$GLOBALS['result']['ok'] = false;
  return false;
} else {
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
  output('Done.');
}
";
      }
    }
    return $php;
  }
}