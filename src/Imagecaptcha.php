<?php

namespace lims\captcha;

class Captcha
{
    private static $_instance = null;
    private        $width;              // 画布宽
    private        $height;             // 画布高
    private        $session_name;       // 存储的
    private        $image;
    private        $font;
    private        $first;
    private        $end;
    private        $fontsize;
    private        $red;
    private        $green;
    private        $blue;
    private        $x;
    private        $y;

    private function __construct($width, $height, $session_name, $fontsize = 14, $red = 120, $green = 120, $blue = 120, $x = 15, $y = )
    {
        $this->width        = $width;
        $this->height       = $height;
        $this->session_name = $session_name;
        $this->font         = dirname(dirname(__FILE__)) . "/font/STHeiti Medium.ttc";
        $this->image        = imagecreatetruecolor($this->width, $this->height);
        if(empty($this->first)) {
            $this->first = rand(1, 20);
            $this->end = rand(1, 20);
        }
        $this->fontsize     = $fontsize;
        $this->red          = $red;
        $this->green        = $green;
        $this->blue         = $blue;
    }

    public static function make($width = 100, $height = 40, $session_name = 'code')
    {
        ob_start();
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($width, $height, $session_name);
        }
        $bgcolor = imagecolorallocate(self::$_instance->image, 255, 255, 255);    // 创建颜色
        imagefill(self::$_instance->image, 0, 0, $bgcolor);                                 // 将背景颜色填进图像区域填充
        self::$_instance->getCode();
        self::$_instance->interfere();
        self::$_instance->out();
    }

    /**
     * 绘制计算式生成和生成结果至session
     */
    private function getCode()
    {
        $fontcolor = imagecolorallocate($this->image, $this->red, $this->green, $this->blue);
        if ($this->first < $this->end) {
            $meth = "bcadd";
            $method_txt = "＋";
        } else {
            $meth = 'bcsub';
            $method_txt = "－";
        }
        $x = (int)($this->width/10)+rand(3, 5);
        $y = rand(15, 18);
        $fonttext = $this->first . " $method_txt " . $this->end . ' = ?';
        $res = $meth($this->first . $this->end);
        imagefttext($this->image, $this->fontsize, 0, $x, $y, $fontcolor, $this->font, $fonttext);
        $_SESSION[$this->session_name] = $res;
    }

    /**
     * 干扰和点
     */
    private function interfere()
    {
        // 干扰点
        for ($i = 0; $i < 60; $i++) {
            $pointcolor = imagecolorallocate($this->image, rand(50, 120), rand(50, 120), rand(50, 120));    // 干扰点的颜色
            imagesetpixel($this->image, rand(0, 100), rand(0, 30), $pointcolor);                            // 画一个单一像素
        }
        // 干扰线
        for ($i = 0; $i < 3; $i++) {
            $linecolor = imagecolorallocate($this->image, rand(80, 220), rand(80, 220), rand(80, 220));     // 干扰线的颜色
            imageline($this->image, rand(1, 99), rand(1, 29), rand(1, 99), rand(1, 29), $linecolor);        // 画一条线段
        }
    }

    /**
     * 输出和销毁
     */
    private function out()
    {
        ob_clean();
        header('content-type:image/png');   // 输出内容格式，输出图像前一定要输出header
        imagepng($this->image);                   // 将$image输出，输出图像
        imagedestroy($this->image);               // 销毁图像
    }
}