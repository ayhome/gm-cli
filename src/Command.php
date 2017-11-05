<?php
namespace ayhome\cli;

class Command {

  //进程名称
  public $name = '';
  //进程名称前缀
  public $prefixName = 'gm-cli';
  //进程全称
  public $fullName = '';

  //定时任务
  public $tick = '';

  public $cmd = 'start';

  //PID文件路径
  public $pidFile = '';
  //PID号
  public $ppid = 0;
  public $daemon = -1;
  public function __construct($cfg = array())
  {
    $shortopts = "d:h:p:n:t:";
    $longopts = ['daemon','host:','port:','cmd:','ac:','name:','tick:'];
    $cmds = getopt($shortopts, $longopts);

    if ($cmds['name']) $this->name = $cmds['name'];
    if ($cmds['n']) $this->name = $cmds['n'];
    if ($cfg['name']) $this->name = $cfg['name'];

    if ($cmds['tick']) $this->tick = $cmds['tick'];
    if ($cmds['t']) $this->tick = $cmds['t'];
    if ($cfg['tick']) $this->tick = $cfg['tick'];

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

    

  }

  public function start($cls = '',$ac = '')
  {
    if (!$this->name) {
      exit("请指定进程名称，参数 -n 或 --name".PHP_EOL);
    }

    if (file_exists($this->pidFile)) {
      $pid = explode("\n", file_get_contents($this->pidFile));
      $cmd = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
      exec($cmd, $out);
      if (!empty($out)) {
        $txt = "{$this->fullName} 已经启动，进程pid为:{$pid[0]}";
        exit(Colors::note($txt));
      } else {
          // echo "警告:sbn-center pid文件 " . $this->pidFile . " 存在，可能sbn-center服务上次异常退出(非守护模式ctrl+c终止造成是最大可能)" . PHP_EOL;
          unlink($this->pidFile);
      }
    }

    $this->fullName = "{$this->prefixName}-{$this->name}";
    $this->pidFile = RUNTIME_PATH."{$this->fullName}.pid";

    if ($this->tick) {
      $this->fullName .=":".$this->tick;
      $cron_arr = explode("-", $this->tick);
      $len = count($cron_arr);
      for ($i=0; $i < (6 - $len); $i++) { 
        $this->tick .="-*";
      }
      $this->tick = str_replace("-", " ", $this->tick);
    }

    if (!is_writable(dirname($this->pidFile))) {
      $txt = "{$this->fullName} pid文件需要目录的写入权限:" . dirname($this->pidFile) ;
      exit(Colors::error($txt));
    }

    $this->ppid = getmypid();
    $r = file_put_contents($this->pidFile, $this->ppid);

    if (function_exists('swoole_set_process_name') && PHP_OS != 'Darwin') {
      swoole_set_process_name($this->fullName);
    }

    if (!file_exists($this->pidFile)) {
      $txt = "{$this->fullName} pid文件生成失败({$this->pidFile}) ,请手动关闭当前启动的{$this->fullName}";
      exit(Colors::note($txt));
    }

    if ($this->cmd == 'status') {
      $this->status();
      return;
    }

    if ($this->tick && $ac  && $cls) {
      $crontab = new Ticker();
      $crontab->When($this->tick)
      ->Then(function () use($cls,$ac){
        // echo(date('Y-m-d H:i:s')." crontab called\n");
        $cls->$ac();
      });
    }else{
      if ($ac  && $cls) {
        $cls->$ac();
      }
    }

    return true;
  }

  public function getCmd($cmd='cmd')
  {
    return $this->$cmd;
  }

  public function find($value='')
  {
    # code...
  }

  public function status($value='')
  {
    $cmd = "ps aux|grep {$this->fullName} |grep -v grep|awk '{print $1, $2, $6, $8, $9, $11}'";
    exec($cmd, $out);
    if (empty($out)) {
      $txt = "没有发现正在运行的{$this->fullName}服务";
      exit(Colors::error($txt));
    }
    echo "USER PID RSS(kb) STAT START COMMAND" . PHP_EOL;
    foreach ($out as $v) {
      echo "\033[31m".$v ."\033[0m". PHP_EOL;
    }
    exit();
  }

  function stop()
  {
    if (!file_exists($this->pidFile)) {
      $txt = "{$this->pidFile}不存在";
      exit(Colors::error($txt));
    }
    $pid = explode("\n", file_get_contents($this->pidFile));
    
    $cmd = "kill {$pid[0]}";
    exec($cmd);
    do {
        $out = [];
        $c = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
        exec($c, $out);
        if (empty($out)) {
          break;
        }
    } while (true);
    //确保停止服务后pid文件被删除
    if (file_exists($this->pidFile)) {
      unlink($this->pidFile);
    }
    $txt = "进程结束成功";
    exit(Colors::note($txt));
    
  }

}