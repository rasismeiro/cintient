<?php

/**
 * Description Framework_Utility
 *
 * @author rasismeiro
 */
class Framework_Utility
{

  public static function isRunning()
  {
    $result = false;
    $processInfo = self::_readPIDFile();
    if (is_array($processInfo) && isset($processInfo['pid']) && isset($processInfo['time'])) {
      if ($processInfo['time'] < 5) {
        $result = true;
      } else {
        if (self::_isPIDRunning($processInfo['pid'])) {
          $result = true;
          self::refreshPIDFile($processInfo['pid']);
        }
      }
    }
    return $result;
  }

  public static function ifRunningThenExits()
  {
    $alreadyRunnig = self::isRunning();
    if ($alreadyRunnig) {
      exit;
    } else {
      self::refreshPIDFile();
    }
  }

  public static function refreshPIDFile($pid='')
  {
    $result = false;
    $filename = self::_getPIDFile();
    if ($filename) {
      $pid = (int) empty($pid) ? getmypid() : $pid;
      $data = $pid . '|' . date('H.i.s.d.m.Y');
      if (false !== file_put_contents($filename, $data)) {
        $result = true;
      }
    }
    return $result;
  }

  private static function _workersDir()
  {
    $result = false;
    $dir = dirname(dirname(dirname(__FILE__))) . '/workers';
    $dir = str_replace(array('\\', '//'), '/', $dir);
    if (is_dir($dir) && is_writable($dir)) {
      $result = $dir;
    } else {
      SystemEvent::raise(SystemEvent::ERROR, " The workers dir is not writable!", __METHOD__);
    }
    return $result;
  }

  private static function _getPIDFile()
  {
    $result = false;
    $dir = self::_workersDir();
    if ($dir) {
      $result = $dir . '/' . strtolower(str_replace('.php', '.pid', basename(__FILE__)));
    }
    return $result;
  }

  private static function _readPIDFile()
  {
    $result = false;

    $pidFile = self::_getPIDFile();
    if ($pidFile && is_readable($pidFile)) {
      list($pid, $_time) = explode('|', trim(file_get_contents($pidFile)));
      list($H, $i, $s, $d, $m, $Y) = explode('.', $_time);
      /* time in seconds */
      $time = (int) (time() - mktime($H, $i, $s, $m, $d, $Y));
      $result = array('pid' => $pid, 'time' => $time);
    }

    return $result;
  }

  private static function _isPIDRunning($pid)
  {
    $pid = (int) sprintf('%d', $pid);
    if (0 === stripos(PHP_OS, 'win')) {
      $cmd = "wmic PROCESS where (ProcessId=$pid) get ProcessId /VALUE | grep \"ProcessId=$pid\" -c";
    } else {
      $cmd = 'ps -eo pid | grep -E "^[ ]*' . $pid . '$" -c';
    }
    $result = ($pid > 0) ? (bool) sprintf('%d', shell_exec($cmd)) : false;
    return $result;
  }

}

?>
