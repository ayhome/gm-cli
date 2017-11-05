
    
**简要描述：** 

定时器，兼容crontab表达式，并精确到秒
`0 0 */20 * * *`
每20分钟执行一次
`*、10 * * * * *`
每10秒执行一次。

**例子** 
```php
//每20分词执行一次，
$crontab = new \ayhome\cli\Ticker();
$crontab->When('0 0 */20 * * *')
->Then(function ($userParams)
{
    echo 'crontab called';
    return false;//return false if you want to cancle this cron.
}, $userParams);
```

```
//cron表达式说明
//表达式 :
//   *                       0     1    2    3    4    5
//   *                       *     *    *    *    *    *
//   *                       -     -    -    -    -    -
//   *                       |     |    |    |    |    |
//   *                       |     |    |    |    |    +----- 每周几 (0 - 6) (Sunday=0)
//   *                       |     |    |    |    +----- 几月 (1 - 12)
//   *                       |     |    |    +------- 几号 (1 - 31)
//   *                       |     |    +--------- 时 (0 - 23)
//   *                       |     +----------- 分 (0 - 59)
//   *                       +------------- 秒 (0-59) (可选)
//当如果使用5个参数的时候则表示最小单位为分钟，即和linux crontab的表达式一样，当使用6个参数时，则表示精确到秒。
```


