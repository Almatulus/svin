<?php

namespace common\components;

use yii\base\Component;

/**
 * Class DummyS3
 *
 * Just for Test cases, imitates `frostealth\yii2\aws\s3/Service` Component
 *
 * @package common\components
 */
class DummyS3 extends Component
{
    public $credentials;
    public $region;
    public $defaultBucket;
    public $defaultAcl;

    public function init()
    {

    }

    public function get(string $filename)
    {

    }

    public function put(string $filename, $body)
    {

    }

    public function delete(string $filename)
    {

    }

    public function upload(string $filename, $source)
    {
        return new DummyS3element([
            'ObjectURL' => 'http://dummy_url/' . $filename,
        ]);
    }

    public function restore(string $filename, int $days)
    {

    }

    public function list(string $prefix)
    {

    }

    public function exist(string $filename)
    {

    }

    public function getUrl(string $filename)
    {

    }

    public function getPresignedUrl(string $filename, $expires)
    {

    }
}

class DummyS3element
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($name)
    {
        return $this->data[$name];
    }
}
