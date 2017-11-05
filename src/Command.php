<?php
namespace ayhome\gmcli;

class Command {

  //进程名称
  public $name = 'cli';
  //进程名称前缀
  public $prefixName = 'gm-cli';
  //进程全称
  public $fullName = '';


  //PID文件路径
  public $pidFile = '';
  //PID号
  public $ppid = 0;
  public $daemon = -1;
  public function __construct($cfg = array())
  {
    $shortopts = "d:h:p:n:";
    $longopts = ['daemon','host:','port:','cmd:','ac:'];
    $cmds = getopt($shortopts, $longopts);

    if ($cmds['name']) $this->name = $cmds['name'];
    if ($cmds['n']) $this->name = $cmds['n'];
    if ($cfg['name']) $this->name = $cfg['name'];

    if ($cmds['daemon']) $this->daemon = $cmds['daemon'];
    if ($cmds['d']) $this->daemon = $cmds['d'];
    if ($cfg['daemon']) $this->daemon = $cfg['d'];

    if ($cmds['port']) $this->port = $cmds['port'];
    if ($cmds['p']) $this->port = $cmds['p'];
    if ($cfg['port']) $this->port = $cfg['port'];

    if ($cmds['host']) $this->host = $cmds['host'];
    if ($cmds['h']) $this->host = $cmds['h'];
    if ($cfg['host']) $this->host = $cfg['host'];
    
    if ($cmds['ac']) $this->ac = $cmds['ac'];
    if ($cfg['ac']) $this->ac = $cfg['ac'];

    if ($cmds['cmd']) $this->cmd = $cmds['cmd'];
    if ($cfg['cmd']) $this->cmd = $cfg['cmd'];

    $this->fullName = "{$this->prefixName}:{$this->name}"
    $this->pidFile = RUNTIME_PATH."{$this->fullName}.pid";

    if (!is_writable(dirname($this->pidFile))) {
      exit("{$this->fullName} pid文件需要目录的写入权限:" . dirname($this->pidFile) . PHP_EOL);
    }

    if (file_exists($this->pidFile)) {
      $pid = explode("\n", file_get_contents($this->pidFile));
      $cmd = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
      exec($cmd, $out);
      if (!empty($out)) {
          exit("{$this->fullName} 已经启动，进程pid为:{$pid[0]}" . PHP_EOL);
      } else {
          // echo "警告:sbn-center pid文件 " . $this->pidFile . " 存在，可能sbn-center服务上次异常退出(非守护模式ctrl+c终止造成是最大可能)" . PHP_EOL;
          unlink($this->pidFile);
      }
    }

    $this->ppid = getmypid();
    file_put_contents($pidFile, $this->ppid);


    if (function_exists('swoole_set_process_name') && PHP_OS != 'Darwin') {
      swoole_set_process_name($this->fullName);
    }

    if (!file_exists($this->pidFile)) {
      exit("{$this->fullName} pid文件生成失败({$this->pidFile}) ,请手动关闭当前启动的{$this->fullName}服务检查原因" . PHP_EOL);
    }
  }

  public function getCmd($cmd='cmd')
  {
    return $this->$cmd;
  }

  public function find($value='')
  {
    # code...
  }

  public function start($value='')
  {
    # code...
  }

  public function start($value='')
  {
    # code...
  }

  public function kill($value='')
  {
    # code...
  }

}