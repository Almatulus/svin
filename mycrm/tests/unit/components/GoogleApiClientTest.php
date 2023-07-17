<?php

use app\tests\fixtures\OrderFixture;

class GoogleApiClientTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->markTestSkipped();
        $this->tester->haveFixtures([
            'order' => [
                'class' => OrderFixture::className(),
            ]
        ]);
    }

    protected function _after()
    {
    }

    public function testEmptyRefreshToken()
    {
        $order = $this->tester->grabFixture('order', "order_1");
        $order->staff->user->google_refresh_token = "";
        expect("Failed event addition", Yii::$app->googleApiClient->addEvent($order))->false();
    }

    public function testAddEvent()
    {
        $order = $this->tester->grabFixture('order', "order_1");
        expect("Successful event addition", Yii::$app->googleApiClient->addEvent($order))->true();
    }

    public function testUpdateEvent()
    {
        $order = $this->tester->grabFixture('order', "order_2");
        expect("Successful event update", Yii::$app->googleApiClient->addEvent($order))->true();
    }
}