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
 *
 * PHP_Depend special task builder element, responsible for invoking
 * PHP_Depend and generating an Overview Pyramid and diagram of analyzed
 * packages. It generates specific interface changes, as all other special
 * tasks.
 *
 * Output from the pdepend.php script invocation:
 *
 *  Usage: pdepend [options] [logger] <dir[,dir[,...]]>
 *
 *    --jdepend-chart=<file>    Generates a diagram of the analyzed packages.
 *    --jdepend-xml=<file>      Generates the package dependency log.
 *
 *    --overview-pyramid=<file> Generates a chart with an Overview Pyramid for the
 *                              analyzed project.
 *
 *    --phpunit-xml=<file>      Generates a metrics xml log that is compatible with
 *                              PHPUnit --log-metrics.
 *
 *    --summary-xml=<file>      Generates a xml log with all metrics.
 *
 *    --coderank-mode=<*[,...]> Used CodeRank strategies. Comma separated list of
 *                              'inheritance'(default), 'property' and 'method'.
 *    --coverage-report=<file>  Clover style CodeCoverage report, as produced by
 *                              PHPUnit's --coverage-clover option.
 *
 *    --configuration=<file>    Optional PHP_Depend configuration file.
 *
 *    --suffix=<ext[,...]>      List of valid PHP file extensions.
 *    --ignore=<dir[,...]>      List of exclude directories.
 *    --exclude=<pkg[,...]>     List of exclude packages.
 *
 *    --without-annotations     Do not parse doc comment annotations.
 *
 *    --debug                   Prints debugging information.
 *    --help                    Print this help text.
 *    --version                 Print the current version.
 *    -d key[=value]            Sets a php.ini value.
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
class Build_BuilderElement_Task_Php_PhpDepend extends Build_BuilderElement
{
  protected $_includeDirs;     // A comma with no spaces separated string with all the dirs to process.
                               // PHP_Depend 0.10.5 has a problem with ~ started dirs
  protected $_excludeDirs;     // A comma with no spaces separated string with all the dirs to ignore.
  protected $_excludePackages; // A comma with no spaces separated string with all package names to ignore.
  protected $_jdependChartFile;
  protected $_overviewPyramidFile;
  protected $_summaryFile;

  public function __construct()
  {
    parent::__construct();
    $this->_includeDirs = null;
    $this->_excludeDirs = null;
    $this->_excludePackages = null;
    $this->_jdependChartFile = null;
    $this->_overviewPyramidFile = null;
    $this->_summaryFile = null;
    $this->_specialTask = 'Build_SpecialTask_PhpDepend';
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    // Let the user specify the include dirs, so that we don't end up
    // PhpDepend processing the whole project (and possibly 3rd party
    // libs) by default
    $o->setIncludeDirs('${sourcesDir}');
    $o->setJdependChartFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_PHPDEPEND_JDEPEND_CHART_FILENAME);
    $o->setOverviewPyramidFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_PHPDEPEND_OVERVIEW_PYRAMID_FILENAME);
    $o->setSummaryFile($GLOBALS['project']->getReportsWorkingDir() . CINTIENT_PHPDEPEND_SUMMARY_FILENAME);
    return $o;
  }

  // TODO
  public function toAnt()
  {
    if (!$this->isActive()) {
      return true;
    }
  }

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
      array('cb' => 'getHtmlFailOnError'),
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'includeDirs',
    		'label' => 'Include dirs',
    		'value' => $this->getIncludeDirs(),
    		'help' => 'Space separated.',
      ),
      array(
        'cb' => 'getHtmlInputText',
    		'name' => 'excludeDirs',
    		'label' => 'Exclude dirs',
    		'value' => $this->getExcludeDirs(),
    		'help' => 'Space separated.',
      ),
      array(
        'cb' => 'getHtmlInputText',
    		'name' => 'excludePackages',
    		'label' => 'Exclude packages',
    		'value' => $this->getExcludePackages(),
    		'help' => 'Space separated.',
      ),
    );
    parent::toHtml(array('title' => 'PhpDepend'), $callbacks);
  }

  // TODO
  public function toPhing()
  {
    if (!$this->isActive()) {
      return true;
    }
  }

  public function toPhp(Array &$context = array())
  {
    if (!$this->isActive()) {
      return '';
    }
    $php              = '';
    $getFailedOnError = $this->getFailOnError() ? "true" : "false";

    if (!$this->getIncludeDirs()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No include dirs set for task PhpDepend.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['result']['task'] = 'phpdepend';
\$_SERVER['argv'] = array('dummyfirstentry');
";
    if ($this->getJdependChartFile()) {
      $php .= "
\$_SERVER['argv'][] = expandStr('--jdepend-chart={$this->getJdependChartFile()}');
";
    }
    if ($this->getOverviewPyramidFile()) {
      $php .= "
\$_SERVER['argv'][] = expandStr('--overview-pyramid={$this->getOverviewPyramidFile()}');
";
    }
    if ($this->getSummaryFile()) {
      $php .= "
\$_SERVER['argv'][] = expandStr('--summary-xml={$this->getSummaryFile()}');
";
    }
    if ($this->getExcludeDirs()) {
      $php .= "
\$_SERVER['argv'][] = expandStr('--ignore=" . str_replace(' ', ',', trim($this->getExcludeDirs())) . "');
";
    }
    if ($this->getExcludePackages()) {
      $php .= "
\$_SERVER['argv'][] = expandStr('--exclude=" . str_replace(' ', ',', trim($this->getExcludePackages())) . "');
";
    }
    // Cintient's space separated to PHP_Depend's comma separated
    $php .= "
\$_SERVER['argv'][] = expandStr('" . str_replace(' ', ',', trim($this->getIncludeDirs())) . "');
";

    $php .= "
// To avoid the 'PHP Notice:  Undefined index: argc' that happens when
// manually triggering a build
\$_SERVER['argc'] = count(\$_SERVER['argv']);
require_once 'PHP/Depend/Autoload.php';
\$autoload = new PHP_Depend_Autoload();
\$autoload->register();
PHP_Depend_Util_Log::setSeverity(-1); // to set PHP_Depend debug to false
ob_start();
\$ret = PHP_Depend_TextUI_Command::main();
output(ob_get_contents());
ob_end_clean();
unset(\$_SERVER['argv']);
unset(\$_SERVER['argc']);
if (\$ret > 0) {
  output('PHP_Depend analysis failed.');
  \$GLOBALS['result']['ok'] = false;
  if (".$getFailedOnError.") {
    return false;
  }
} else {
  output('PHP_Depend analysis successful.');
	\$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
}
";
    return $php;
  }
}