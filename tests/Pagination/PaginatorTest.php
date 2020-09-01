<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SemelaPavel\Pagination\Paginator;
use PHPUnit\Framework\TestCase;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * @version 2020-08-03
 */
final class PaginatorTest extends TestCase
{
    // Constants for all tests except setters methods tests!
    const ITEMS = 100;
    const PER_PAGE = 15;
    const FIRST_PAGE = 1;
    const LAST_PAGE = 7;
    const LAST_PAGE_ITEMS = 10;
    const FIRST_PAGE_OFFSET = 0;
    const LAST_PAGE_OFFSET = 90;
    
    protected $pN;
    protected $pF;
    protected $pL;
    
    protected function setUp(): void
    {
        $this->pN = new Paginator(null, null, null);
        $this->pF = new Paginator(self::ITEMS, self::PER_PAGE, self::FIRST_PAGE);        
        $this->pL = new Paginator(self::ITEMS, self::PER_PAGE, self::LAST_PAGE);
    }
    
    public function testConstruct()
    {
        $this->assertSame(0, $this->pN->getNumOfItems());
        $this->assertSame(1, $this->pN->getItemsPerPage());
        $this->assertSame(self::FIRST_PAGE, $this->pN->getCurrentPage());
        $this->assertSame(1, $this->pN->getNumOfPages());
        
        $this->assertSame(self::ITEMS, $this->pF->getNumOfItems());
        $this->assertSame(self::PER_PAGE, $this->pF->getItemsPerPage());
        $this->assertSame(self::FIRST_PAGE, $this->pF->getCurrentPage());
        $this->assertSame(self::LAST_PAGE, $this->pF->getNumOfPages());
        
        $this->assertSame(self::ITEMS, $this->pL->getNumOfItems());
        $this->assertSame(self::PER_PAGE, $this->pL->getItemsPerPage());
        $this->assertSame(self::LAST_PAGE, $this->pL->getCurrentPage());
        $this->assertSame(self::LAST_PAGE, $this->pL->getNumOfPages());
    }
    
    public function testGetCurrentPageLength()
    {
        $this->assertSame(0, $this->pN->getCurrentPageLength());
        $this->assertSame(self::PER_PAGE, $this->pF->getCurrentPageLength());
        $this->assertSame(self::LAST_PAGE_ITEMS, $this->pL->getCurrentPageLength());        
    }
    
    public function testGetOffset()
    {
        $this->assertSame(0, $this->pN->getOffset());
        $this->assertSame(self::FIRST_PAGE_OFFSET, $this->pF->getOffset());
        $this->assertSame(self::LAST_PAGE_OFFSET, $this->pL->getOffset());
    }
    
    public function testGetFirstPage()
    {
        $this->assertSame(self::FIRST_PAGE, $this->pN->getFirstPage());
        $this->assertSame(self::FIRST_PAGE, $this->pF->getFirstPage());
        $this->assertSame(self::FIRST_PAGE, $this->pL->getFirstPage());
    }
    
    public function testGetLastPage()
    {
        $this->assertSame(self::FIRST_PAGE, $this->pN->getLastPage());
        $this->assertSame(self::LAST_PAGE, $this->pF->getLastPage());
        $this->assertSame(self::LAST_PAGE, $this->pL->getLastPage());
    }
    
    public function testIsFirst()
    {
        $this->assertTrue($this->pN->isFirst());
        $this->assertTrue($this->pF->isFirst());
        $this->assertFalse($this->pL->isFirst());
    }
    
    public function testIsLast()
    {
        $this->assertTrue($this->pN->isLast());
        $this->assertFalse($this->pF->isLast());
        $this->assertTrue($this->pL->isLast());
    }
    
    public function testGetNextPage()
    {
        $this->assertSame(null, $this->pN->getNextPage());
        $this->assertSame(self::FIRST_PAGE + 1, $this->pF->getNextPage());
        $this->assertSame(null, $this->pL->getNextPage());
    }
    
    public function testGetPrevPage()
    {
        $this->assertSame(null, $this->pN->getPrevPage());
        $this->assertSame(null, $this->pF->getPrevPage());
        $this->assertSame(self::LAST_PAGE - 1, $this->pL->getPrevPage());
    }
    
    public function testSetNumOfItems()
    {
        $this->pN->setNumOfItems(100);
        $this->assertSame(100, $this->pN->getNumOfItems());
        $this->assertSame(100, $this->pN->getNumOfPages());
        
        $this->pF->setNumOfItems(200);
        $this->assertSame(200, $this->pF->getNumOfItems());
        $this->assertSame(14, $this->pF->getNumOfPages());
    }
    
    public function testSetItemsPerPage()
    {
        $this->pN->setItemsPerPage(10);
        $this->assertSame(1, $this->pN->getNumOfPages());
        
        $this->pF->setItemsPerPage(11);
        $this->assertSame(10, $this->pF->getNumOfPages());
    }
    
    public function testSetCurrentPage()
    {
        $this->pN->setCurrentPage(10);
        $this->assertSame(self::FIRST_PAGE, $this->pN->getCurrentPage());
        
        $this->pF->setCurrentPage(2);
        $this->assertSame(2, $this->pF->getCurrentPage());
        
        $this->pF->setCurrentPage(0);
        $this->assertSame(self::FIRST_PAGE, $this->pF->getCurrentPage());
        
        $this->pL->setCurrentPage(999);
        $this->assertSame(self::LAST_PAGE, $this->pL->getCurrentPage());
    }
}
