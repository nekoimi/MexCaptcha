# MexCaptcha —— Laravel / Lumen

本项目修改自 [Captcha for Laravel 5](https://github.com/mewebstudio/captcha).

![20190810230258.png](https://i.loli.net/2019/08/10/rGwqJIPdiMCy67v.png)


### 安装

```bash
composer require yprisoner/mexcaptcha -vvv
```

- php >= 7.0

- 添加`mexcaptcha`配置文件

bootstrap/app.php 添加

```php
$app->configure('mexcaptcha');
```

```php
$app->register(MexCaptcha\Providers\MexCaptchaServiceProvider::class);
```

### 缓存

- 使用自定义缓存, 实现`CacheHandlerInterface`接口

- `mexcaptcha`配置中添加缓存

### Example

```php
'cache_handler' =>  \App\Handler\CaptchaCacheHandler::class // implements MexCaptcha\Contracts\CacheHandlerInterface
```

```php
<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   2019/8/10 21:37
 * #                            ------
 **/
namespace App\Handler;
use MexCaptcha\Contracts\CacheHandlerInterface;

class CaptchaCacheHandler implements CacheHandlerInterface {

    /**
     * Get cache value by cache Key.
     *
     * @param string $key
     * @return string
     */
    public function get (string $key): string {
        // TODO: Implement get() method.
    }

    /**
     * Set a cache.
     *
     * @param string $key
     * @param string $value
     * @param int $expired_at
     * @return mixed
     */
    public function set (string $key, string $value, int $expired_at) {
        // TODO: Implement set() method.
    }

    /**
     * Determine if the cache exists.
     *
     * @param string $key
     * @return bool
     */
    public function has (string $key): bool {
        // TODO: Implement has() method.
    }

    /**
     * Remove s cache by Key.
     *
     * @param string $key
     * @return mixed
     */
    public function del (string $key) {
        // TODO: Implement del() method.
    }
}

```

### 使用

```php
<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   2019/8/10 21:39
 * #                            ------
 **/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MexCaptcha\Contracts\CaptchaInterface;
use MexCaptcha\Support\Str;

class CaptchaController extends Controller {


    /**
     * @param Request $request
     * @return array
     */
    public function getCaptchaInfo (Request $request) {
        // ......
        $captcha_id = Str::generate_uuid();
        return [
            'captcha_url' => "http://192.168.0.100:8000/captcha/{$captcha_id}",
            'captcha_id'        => $captcha_id
        ];
    }


    /**
     * 显示验证码图片
     *
     * @param Request $request
     * @param string $captcha_id
     * @return mixed
     */
    public function showCaptchaImage (Request $request, string $captcha_id) {
        /**@var CaptchaInterface $mexcaptcha */
        $mexcaptcha = app('mexcaptcha');
        return $mexcaptcha->create($captcha_id);
    }


    /**
     * @param Request $request
     * @param string $captcha_id
     * @return array
     */
    public function doSomeThing (Request $request, string $captcha_id) {
        $validator = Validator::make($request->all(), [
            'captcha_code' => 'required|string|mexcaptcha:' . $captcha_id
        ], [
                     'captcha_code.mexcaptcha'   =>  '验证码错误.'
        ]);
        if ($validator->fails()) {
            return [
                'code'  =>  -1,
                'message'   =>  $validator->errors()
            ];
        }
        return [
            'code'    => 0,
            'message' => 'Hello World.'
        ];
    }

}
```
