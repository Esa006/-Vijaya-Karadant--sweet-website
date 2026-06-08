<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PromotionServiceTest extends TestCase {
    
    public function testGetPromotionSuccess(): void {
        $mockRepo = $this->createMock(PromotionRepository::class);
        
        $mockRepo->expects($this->once())
            ->method('getPromotionBySectionId')
            ->with('festival-offers')
            ->willReturn([
                'title' => 'Special Festival Offers',
                'description' => 'Up to 30% Off on all sweets'
            ]);

        $service = new PromotionService($mockRepo);
        $result = $service->getPromotion('festival-offers');

        $this->assertEquals('Special Festival Offers', $result['title']);
        $this->assertEquals('Up to 30% Off on all sweets', $result['description']);
    }

    public function testGetPromotionReturnsFallbackOnNull(): void {
        $mockRepo = $this->createMock(PromotionRepository::class);
        
        $mockRepo->expects($this->once())
            ->method('getPromotionBySectionId')
            ->with('festival-offers')
            ->willReturn(null);

        $service = new PromotionService($mockRepo);
        $result = $service->getPromotion('festival-offers');

        $this->assertEquals('Vibrant Festival Offers', $result['title']);
        $this->assertEquals('Celebrate with Sweet Savings', $result['subtitle']);
    }

    public function testGetPromotionReturnsFallbackOnException(): void {
        $mockRepo = $this->createMock(PromotionRepository::class);
        
        $mockRepo->expects($this->once())
            ->method('getPromotionBySectionId')
            ->with('festival-offers')
            ->willThrowException(new Exception("Database connection failure"));

        $service = new PromotionService($mockRepo);
        
        $result = $service->getPromotion('festival-offers');

        $this->assertEquals('Vibrant Festival Offers', $result['title']);
        $this->assertEquals('Celebrate with Sweet Savings', $result['subtitle']);
    }
}
