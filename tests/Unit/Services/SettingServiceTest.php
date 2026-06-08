<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SettingServiceTest extends TestCase {
    
    public function testGetAllSettings(): void {
        $mockRepo = $this->createMock(SettingRepository::class);
        $mockRepo->expects($this->once())
            ->method('getAll')
            ->willReturn([
                'store_name' => 'Vijaya Karadant Store',
                'store_phone' => '+91 98860 24567'
            ]);

        $service = new SettingService($mockRepo);
        $result = $service->getAllSettings();

        $this->assertCount(2, $result);
        $this->assertEquals('Vijaya Karadant Store', $result['store_name']);
        $this->assertEquals('+91 98860 24567', $result['store_phone']);
    }

    public function testGetSettingsByGroup(): void {
        $mockRepo = $this->createMock(SettingRepository::class);
        $mockRepo->expects($this->once())
            ->method('getByGroup')
            ->with('payments')
            ->willReturn([
                'pay_upi_id' => 'vijayakaradant@upi'
            ]);

        $service = new SettingService($mockRepo);
        $result = $service->getSettingsByGroup('payments');

        $this->assertCount(1, $result);
        $this->assertEquals('vijayakaradant@upi', $result['pay_upi_id']);
    }

    public function testSaveSettingsSuccess(): void {
        $mockRepo = $this->createMock(SettingRepository::class);
        
        $mockRepo->expects($this->exactly(2))
            ->method('update')
            ->willReturnMap([
                ['store_name', 'New Name', 'store', true],
                ['pay_upi_id', 'new@upi', 'payments', true]
            ]);

        $service = new SettingService($mockRepo);
        $result = $service->saveSettings([
            'store_name' => 'New Name',
            'pay_upi_id' => 'new@upi'
        ]);

        $this->assertTrue($result);
    }

    public function testSaveSettingsEmptyDataThrowsException(): void {
        $mockRepo = $this->createMock(SettingRepository::class);
        $service = new SettingService($mockRepo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Settings data cannot be empty");

        $service->saveSettings([]);
    }

    public function testSaveSettingsEmptyKeyThrowsException(): void {
        $mockRepo = $this->createMock(SettingRepository::class);
        $service = new SettingService($mockRepo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Setting key cannot be empty");

        $service->saveSettings([
            '   ' => 'some value'
        ]);
    }
}
