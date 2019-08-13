<?php
/**
 * # ----
 * #     Yprisoner <yyprisoner@gmail.com>
 * #                   19-8-9 下午4:28
 * #                            ------
 **/

namespace MexCaptcha;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use MexCaptcha\Contracts\CaptchaInterface;
use Intervention\Image\ImageManager;
use MexCaptcha\Contracts\CacheHandlerInterface;
use MexCaptcha\Exceptions\CaptchaException;

/**
 * Class MexCaptcha
 * @package MexCaptcha
 *
 * 验证码生成
 */
class MexCaptcha implements CaptchaInterface {

    const CACHE_PREFIX = 'mex_captcha:';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var ImageManager
     */
    protected $imageManager;


    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * @var Str
     */
    protected $str;

    /**
     * @var ImageManager->canvas
     */
    protected $canvas;

    /**
     * @var ImageManager->image
     */
    protected $image;

    /**
     * @var array
     */
    protected $backgrounds = [];

    /**
     * @var array
     */
    protected $fonts = [];

    /**
     * @var array
     */
    protected $fontColors = [];

    /**
     * @var int
     */
    protected $length = 5;

    /**
     * @var int
     */
    protected $width = 180;

    /**
     * @var int
     */
    protected $height = 50;

    /**
     * @var int
     */
    protected $angle = 15;

    /**
     * @var int
     */
    protected $lines = 3;

    /**
     * @var string
     */
    protected $characters;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $contrast = 0;

    /**
     * @var int
     */
    protected $quality = 90;

    /**
     * @var int
     */
    protected $sharpen = 0;

    /**
     * @var int
     */
    protected $blur = 0;

    /**
     * @var bool
     */
    protected $bgImage = true;

    /**
     * @var string
     */
    protected $bgColor = '#ffffff';

    /**
     * @var bool
     */
    protected $sensitive = false;

    protected $captchaConf;

    /**
     * @var CacheHandlerInterface
     */
    protected $cacheHandler;

    /**
     * Constructor
     *
     * @param Filesystem $files
     * @param Repository $config
     * @param ImageManager $imageManager
     * @param Hasher $hasher
     * @param Str $str
     * @throws CaptchaException
     * @internal param Validator $validator
     */
    public function __construct (
        Filesystem $files,
        Repository $config,
        ImageManager $imageManager,
        Hasher $hasher,
        Str $str
    ) {
        $this->captchaConf = config('mexcaptcha');//加载配置
        $this->sensitive = isset($this->captchaConf['sensitive']) ? $this->captchaConf['sensitive'] : false;
        $this->files = $files;
        $this->config = $config;
        $this->imageManager = $imageManager;
        $this->hasher = $hasher;
        $this->str = $str;
        $this->characters = isset($this->captchaConf['captcha_characters']) ?
            $this->captchaConf['captcha_characters'] :
            '2346789abcdefghjmnpqrtuxyzABCDEFGHJMNPQRTUXYZ';

        $cache_handler_class = $this->captchaConf['cache_handler'];
        if (null === $cache_handler_class || !class_exists($cache_handler_class)) {
            throw new CaptchaException('CacheHandler not exists!');
        }
        $cache_handler = app($cache_handler_class);
        if (! $cache_handler instanceof CacheHandlerInterface) {
            throw new CaptchaException('CacheHandler not valid `CacheHandlerInterface`!');
        }
        $this->cacheHandler = $cache_handler;
    }

    /**
     * @return void
     */
    protected function configure () {
        if (!empty($this->captchaConf) && isset($this->captchaConf['options'])) {
            foreach ($this->captchaConf['options'] as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * Image backgrounds
     *
     * @return string
     */
    protected function background () {
        return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
    }

    /**
     * Generate captcha text by id
     * @param string $captcha_id
     * @return string
     */
    private function generate (string $captcha_id) {
        $characters = str_split($this->characters);
        $captcha_value = '';
        for ($i = 0; $i < $this->length; $i++) {
            $captcha_value .= $characters[rand(0, count($characters) - 1)];
        }
        $captcha_id = self::CACHE_PREFIX . $captcha_id;
        //缓存时间
        $cacheTime = isset($this->captchaConf['expired_at']) ? $this->captchaConf['expired_at'] : 10;
        $this->cacheHandler->set($captcha_id, $captcha_value, $cacheTime);
        return $captcha_value;
    }


    /**
     * @param string $captcha_id
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function create (string $captcha_id, int $width = 0, int $height = 0) {
        $this->backgrounds = $this->files->files(__DIR__ . '/../assets/backgrounds');
        $this->fonts = $this->files->files(__DIR__ . '/../assets/fonts');
        $this->fonts = array_values($this->fonts); //reset fonts array index
        $this->fonts = array_map(function($file) {
            return $file->getPathname();
        }, $this->fonts);
        $this->configure();
        foreach (array_merge(
            compact('width'),
            compact('height')) as $k => $v) {
            if ($v > 0) {
                $this->{$k} = $v;
            }
        }
        $this->text = $this->generate($captcha_id);
        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );

        if ($this->bgImage) {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        } else {
            $this->image = $this->canvas;
        }

        if ($this->contrast != 0) {
            $this->image->contrast($this->contrast);
        }

        $this->text();

        $this->lines();

        if ($this->sharpen) {
            $this->image->sharpen($this->sharpen);
        }

        if ($this->blur) {
            $this->image->blur($this->blur);
        }

        return $this->image->response('png', $this->quality);
    }

    /**
     * Captcha check by id
     * @param string $captcha_id
     * @param string $input_value
     * @return bool
     */
    function checkCaptcha (string $captcha_id, string $input_value): bool {
        $captcha_cache_key = self::CACHE_PREFIX . $captcha_id;
        if (!$this->cacheHandler->has($captcha_cache_key)) {
            return false;
        }

        $captcha_value = $this->cacheHandler->get($captcha_cache_key);
        if (!$this->sensitive) {
            $captcha_value = strtolower(strval($captcha_value));
            $input_value = strtolower(strval($input_value));
        }
        if ($input_value === $captcha_value) {
            $this->cacheHandler->del($captcha_cache_key);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Writing captcha text
     */
    protected function text () {
        $marginTop = $this->image->height() / $this->length;

        $i = 0;
        foreach (str_split($this->text) as $char) {
            $marginLeft = ($i * $this->image->width() / $this->length) + 10;

            $this->image->text($char, $marginLeft, $marginTop, function ($font) {
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });

            $i++;
        }
    }

    /**
     * Image fonts
     *
     * @return string
     */
    protected function font () {
        return $this->fonts[rand(0, count($this->fonts) - 1)];
    }

    /**
     * Random font size
     *
     * @return integer
     */
    protected function fontSize () {
        return rand($this->image->height() - 10, $this->image->height());
    }

    /**
     * Random font color
     *
     * @return array
     */
    protected function fontColor () {
        if (!empty($this->fontColors)) {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        } else {
            $color = [rand(0, 255), rand(0, 255), rand(0, 255)];
        }

        return $color;
    }

    /**
     * Angle
     *
     * @return int
     */
    protected function angle () {
        return rand((-1 * $this->angle), $this->angle);
    }

    /**
     * Random image lines
     *
     * @return ImageManager
     */
    protected function lines () {
        for ($i = 0; $i <= $this->lines; $i++) {
            $this->image->line(
                rand(0, $this->image->width()) + $i * rand(0, $this->image->height()),
                rand(0, $this->image->height()),
                rand(0, $this->image->width()),
                rand(0, $this->image->height()),
                function ($draw) {
                    $draw->color($this->fontColor());
                }
            );
        }
        return $this->image;
    }

}
