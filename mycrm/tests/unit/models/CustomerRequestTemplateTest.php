<?php

namespace tests\codeception\unit\models;

use core\models\customer\CustomerRequestTemplate;
use Codeception\Specify;

/**
 * @property CustomerRequestTemplate $_model
 */
class CustomerRequestTemplateTest extends \Codeception\TestCase\Test
{
    use Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    private $_model;

    protected function _before()
    {
        $this->_model = new CustomerRequestTemplate();
    }

    // tests
    public function testValidation()
    {
        $this->specify('check empty values', function() {
            $this->_model->key = null;
            $this->_model->is_enabled = null;
            $this->_model->template = null;
            $this->_model->company_id = null;
            $this->_model->description = null;
            expect('Validation should fail', $this->_model->validate())->false();
            expect('Check key empty error', $this->_model->getErrors())->hasKey('key');
            expect('Check is_enabled empty error', $this->_model->getErrors())->hasntKey('is_enabled');
            expect('Check company_id repeat error', $this->_model->getErrors())->hasKey('company_id');
            expect('Check description empty error', $this->_model->getErrors())->hasntKey('description');
        });
        $this->specify('check empty values', function() {
            $this->_model->key = null;
            $this->_model->is_enabled = true;
            $this->_model->template = null;
            $this->_model->company_id = null;
            $this->_model->description = null;
            expect('Validation should fail', $this->_model->validate())->false();
            expect('Check key empty error', $this->_model->getErrors())->hasKey('key');
            expect('Check template empty error', $this->_model->getErrors())->hasKey('template');
            expect('Check company_id repeat error', $this->_model->getErrors())->hasKey('company_id');
            expect('Check description empty error', $this->_model->getErrors())->hasntKey('description');
        });
    }

}