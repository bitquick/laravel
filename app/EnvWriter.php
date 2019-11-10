<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 10/27/2019
 * Time: 10:17 PM
 */

namespace App;


class EnvWriter
{
    protected $file;

    protected $type = 'env';

    private const AVAILABLE_TYPES = [
        'env', 'ini'
    ];

    private const COMMENT = [
        'env' => '#',
        'ini' => ';'
    ];

    public function __construct(string $file, $type = null)
    {
        if (!empty($type) && in_array($type, self::AVAILABLE_TYPES)) {
            $this->type = $type;
        }

        $this->file = $file;
    }

    public function write($key, $value) {
        file_put_contents($this->file, preg_replace(
            $this->replacementPattern($key),
            $this->replacement($key, $value),
            file_get_contents($this->file)
        ));
    }

    public function replacementPattern($field) {
        return "/^{$field}=.*/m";
    }

    public function replacement($key, $value) {
        return "{$key}=".$value;
    }

    public function comment($key) {

    }

    public function uncomment($key) {
        $comment = self::COMMENT[$this->type];

        $preg_key = preg_quote($key, '/');

        $pattern = "/{$comment} *{$preg_key}=/";

        file_put_contents($this->file, preg_replace(
            $pattern,
            "{$key}=",
            file_get_contents($this->file)
        ));
    }
}
