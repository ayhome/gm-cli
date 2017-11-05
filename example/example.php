<?php
namespace app\push\controller;


use think\Db;
use think\helper;
use think\Cache;

use ayhome\cli\Colors as command;
use ayhome\cli\Ticker;
class Jinhua extends Common
{

  public function index($value='')
  {
    $cmd = new \ayhome\cli\Command();
    $cmd->prefixName = 'push';
    $cmd->name = 'Jinhua';
    $cmd->start();

    $crontab = new Ticker();
    $crontab->When('0 0 */20 * * *')
    ->Then(function (){
      $this->apstatus();
    });


    $crontab2 = new Ticker();
    $crontab2->When('*/5 * * * * *')
    ->Then(function (){
      $this->mac();
    });


  }

  public function index2()
  {
    $cmd = new \ayhome\cli\Command();
    $ac = $cmd->getCmd('ac');
    $cmd->name = $ac;

    if ($ac && method_exists($this,$ac)) {
      $cls = new \app\push\controller\Jinhua();
      $cmd->start($cls,$ac);
      // action('Jinhua/'.$ac);
    }else{
      exit(command::error("{$ac}方法不存在"));
    }
  }

}
