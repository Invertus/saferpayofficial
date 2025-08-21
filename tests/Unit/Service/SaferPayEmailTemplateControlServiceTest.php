<?php

declare(strict_types=1);

namespace Invertus\SaferPay\Tests\Unit\Service;

use Invertus\SaferPay\Service\SaferPayEmailTemplateControlService;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class SaferPayEmailTemplateControlServiceTest extends UnitTestCase
{
    public function testShouldSendEmailReturnsTrueForNonSaferPayOrder()
    {
        $service = new SaferPayEmailTemplateControlService();
        $params = [
            'cart' => (object) ['id' => 1],
            'template' => 'new_order',
        ];

        $result = $service->shouldSendEmail($params);

        $this->assertTrue($result);
    }

    public function testShouldSendEmailReturnsTrueForNonControlledTemplate()
    {
        $service = new SaferPayEmailTemplateControlService();
        $params = [
            'cart' => (object) ['id' => 1],
            'template' => 'shipped',
        ];

        $result = $service->shouldSendEmail($params);

        $this->assertTrue($result);
    }

    public function testShouldSendEmailReturnsTrueWhenCartNotSet()
    {
        $service = new SaferPayEmailTemplateControlService();
        $params = [
            'template' => 'new_order',
        ];

        $result = $service->shouldSendEmail($params);

        $this->assertTrue($result);
    }
}
