<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\HookSubscribers\PagePreviewRenderer;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\PageLayoutController;

/**
 * Class PageControllerTest
 */
class PagePreviewRendererTest extends AbstractTestCase
{
    public function testRenderWithoutRecordReturnsEmptyString(): void
    {
        $pageProvider = $this->getMockBuilder(PageProvider::class)
            ->setMethods(['getForm'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject = $this->getMockBuilder(PagePreviewRenderer::class)
            ->setMethods(['getPageProvider', 'getRecord', 'getForm'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getPageProvider')->willReturn($pageProvider);
        $subject->method('getRecord')->willReturn(['uid' => 123]);
        $subject->method('getForm')->willReturn(Form::create());

        $pageLayoutController = $this->getMockBuilder(PageLayoutController::class)->disableOriginalConstructor()->getMock();
        $pageLayoutController->id = 123;

        $result = $subject->render([], $pageLayoutController);
        self::assertSame('', $result);
    }

    /**
     * @param ProviderInterface $provider
     * @param string $expected
     * @test
     * @dataProvider getRenderTestValues
     */
    public function testRender(ProviderInterface $provider, $expected)
    {
        $subject = $this->getMockBuilder(PagePreviewRenderer::class)->setMethods(['getPageProvider', 'getRecord'])->disableOriginalConstructor()->getMock();
        $subject->expects($this->once())->method('getPageProvider')->willReturn($provider);
        $subject->expects($this->once())->method('getRecord')->with(123)->willReturn(['uid' => 123]);
        $pageLayoutController = $this->getMockBuilder(PageLayoutController::class)->disableOriginalConstructor()->getMock();
        $pageLayoutController->id = 123;
        $result = $subject->render([], $pageLayoutController);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderTestValues()
    {
        $withForm = $this->getMockBuilder(PageProvider::class)->setMethods(['getPreview', 'getForm'])->getMock();
        $withForm->method('getPreview')->willReturn(['', 'foobarpreview1', '']);
        $withForm->method('getForm')->willReturn($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());
        $withDisabledForm = $this->getMockBuilder(PageProvider::class)->setMethods(['getPreview', 'getForm'])->getMock();
        $withDisabledForm->method('getPreview')->willReturn(['', 'foobarpreview2', '']);
        $withDisabledForm->method('getForm')->willReturn(Form::create(['enabled' => false]));
        $withPreview = $this->getMockBuilder(PageProvider::class)->setMethods(['getForm', 'getPreview'])->getMock();
        $withPreview->expects($this->once())->method('getPreview')->willReturn([null, 'preview', true]);
        $withPreview->expects($this->once())->method('getForm')->willReturn($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());

        return [
            [$withForm, 'foobarpreview1'],
            [$withDisabledForm, ''],
            [$withPreview, 'preview'],
        ];
    }
}
