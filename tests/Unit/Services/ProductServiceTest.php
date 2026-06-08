<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase {

    private function getMockPDO(): PDO {
        $mockDb = $this->createMock(PDO::class);
        $mockDb->method('beginTransaction')->willReturn(true);
        $mockDb->method('commit')->willReturn(true);
        $mockDb->method('rollBack')->willReturn(true);
        return $mockDb;
    }

    public function testGetFeaturedProductsSuccess(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('getFeaturedProducts')
            ->with(3)
            ->willReturn([
                ['id' => 1, 'name' => 'Premium Karadant', 'slug' => 'premium-karadant']
            ]);

        $service = new ProductService($mockRepo, $this->getMockPDO());
        $result = $service->getFeaturedProducts(3);

        $this->assertCount(1, $result);
        $this->assertEquals('Premium Karadant', $result[0]['name']);
    }

    public function testGetFeaturedProductsError(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('getFeaturedProducts')
            ->willThrowException(new Exception("Database error"));

        $service = new ProductService($mockRepo, $this->getMockPDO());
        $result = $service->getFeaturedProducts(3);

        $this->assertEmpty($result);
    }

    public function testGetProductBySlugSuccess(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('getBySlug')
            ->with('premium-vijaya-karadant')
            ->willReturn([
                'id' => 1,
                'name' => 'Premium Vijaya Karadant',
                'slug' => 'premium-vijaya-karadant',
                'image_path' => 'assets/images/test.png'
            ]);

        $service = new ProductService($mockRepo, $this->getMockPDO());
        $result = $service->getProductBySlug('premium-vijaya-karadant');

        $this->assertNotNull($result);
        $this->assertEquals('Premium Vijaya Karadant', $result['name']);
        $this->assertArrayHasKey('ingredients', $result);
    }

    public function testGetProductBySlugComboFallback(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('getBySlug')
            ->willReturn(null);

        $mockComboRepo = $this->createMock(ComboRepository::class);
        $mockComboRepo->expects($this->once())
            ->method('getBySlug')
            ->with('combo-slug')
            ->willReturn([
                'id' => 5,
                'name' => 'Sweet Combo',
                'slug' => 'combo-slug',
                'price' => 500.0,
                'items' => []
            ]);

        $service = new ProductService($mockRepo, $this->getMockPDO(), null, null, $mockComboRepo);
        $result = $service->getProductBySlug('combo-slug');

        $this->assertNotNull($result);
        $this->assertEquals('Sweet Combo', $result['name']);
        $this->assertTrue($result['is_combo']);
    }

    public function testCreateProductSuccess(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('create')
            ->willReturn(10);

        $mockAudit = $this->createMock(AuditService::class);
        $mockAudit->expects($this->once())
            ->method('log')
            ->willReturn(true);

        $mockDb = $this->getMockPDO();

        $service = new ProductService($mockRepo, $mockDb, null, $mockAudit);
        
        $productId = $service->createProduct([
            'name' => 'New Product',
            'base_price' => 100,
            'stock_quantity' => 50
        ]);

        $this->assertEquals(10, $productId);
    }

    public function testCreateProductThrowsSkuConflictException(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('create')
            ->willThrowException(new PDOException("Duplicate entry 'PROD123' for key 'sku'", 23000));

        $mockDb = $this->getMockPDO();

        $service = new ProductService($mockRepo, $mockDb);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The SKU 'PROD123' is already in use by another product.");

        $service->createProduct([
            'name' => 'Duplicate Product',
            'sku' => 'PROD123'
        ]);
    }

    public function testUpdateProductSuccess(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn(['id' => 1, 'name' => 'Old Name']);

        $mockRepo->expects($this->once())
            ->method('update')
            ->with(1, $this->callback(function($data) {
                return $data['name'] === 'Updated Name';
            }))
            ->willReturn(true);

        $mockAudit = $this->createMock(AuditService::class);
        $mockAudit->expects($this->once())
            ->method('log')
            ->willReturn(true);

        $service = new ProductService($mockRepo, $this->getMockPDO(), null, $mockAudit);
        $success = $service->updateProduct(1, [
            'name' => 'Updated Name'
        ]);

        $this->assertTrue($success);
    }

    public function testUpdateProductNotFoundReturnsFalse(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $service = new ProductService($mockRepo, $this->getMockPDO());
        $success = $service->updateProduct(999, [
            'name' => 'New Name'
        ]);

        $this->assertFalse($success);
    }

    public function testDeleteProductSuccess(): void {
        $mockRepo = $this->createMock(ProductRepository::class);
        $mockRepo->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $mockAudit = $this->createMock(AuditService::class);
        $mockAudit->expects($this->once())
            ->method('log')
            ->willReturn(true);

        $service = new ProductService($mockRepo, $this->getMockPDO(), null, $mockAudit);
        $success = $service->deleteProduct(1);

        $this->assertTrue($success);
    }
}
