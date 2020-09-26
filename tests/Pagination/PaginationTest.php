<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SemelaPavel\Pagination\Pagination;
use PHPUnit\Framework\TestCase;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class PaginationTest extends TestCase
{
    const ITEMS = 100;
    const PER_PAGE = 5;
    
    protected $p1, $p2, $p3, $p4, $p5, $p6;
    protected $p15, $p16, $p17, $p18, $p19, $p20;
    protected $pS1, $pS2, $pS2b, $pS3;

    protected function setUp(): void
    {
        $this->p1 = new Pagination(self::ITEMS, self::PER_PAGE, 1);
        $this->p2 = new Pagination(self::ITEMS, self::PER_PAGE, 2);
        $this->p3 = new Pagination(self::ITEMS, self::PER_PAGE, 3);
        $this->p4 = new Pagination(self::ITEMS, self::PER_PAGE, 4);
        $this->p5 = new Pagination(self::ITEMS, self::PER_PAGE, 5);
        $this->p6 = new Pagination(self::ITEMS, self::PER_PAGE, 6);
        $this->p15 = new Pagination(self::ITEMS, self::PER_PAGE, 15);
        $this->p16 = new Pagination(self::ITEMS, self::PER_PAGE, 16);
        $this->p17 = new Pagination(self::ITEMS, self::PER_PAGE, 17);
        $this->p18 = new Pagination(self::ITEMS, self::PER_PAGE, 18);
        $this->p19 = new Pagination(self::ITEMS, self::PER_PAGE, 19);
        $this->p20 = new Pagination(self::ITEMS, self::PER_PAGE, 20);
        
        $this->pS1 = new Pagination(20, 20, 1);
        $this->pS2 = new Pagination(20, 10, 2);
        $this->pS2b = new Pagination(21, 7, 2);
        $this->pS3 = new Pagination(21, 7, 3);
    }
    
    public function pArray($pagesNumbers, $currentPage)
    {
        $pagination = [];
        
        foreach ($pagesNumbers as $page) {
            $pagination[] = [
                'page' => $page, 
                'isCurrent' => ($page === $currentPage)
            ];
        }
        
        return $pagination;
    }
    
    public function testToArrayExample1()
    {
        $outerRange = 1; 
        $innerRange = 2;
        $gap = null;
        
        $pE1 = $this->pArray([1, 2, 3, 4, 5, $gap, 20], 1);
        $pE2 = $this->pArray([1, 2, 3, 4, 5, $gap, 20], 2);
        $pE3 = $this->pArray([1, 2, 3, 4, 5, $gap, 20], 3);
        $pE4 = $this->pArray([1, 2, 3, 4, 5, 6, $gap, 20], 4);
        $pE5 = $this->pArray([1, $gap, 3, 4, 5, 6, 7, $gap, 20], 5);
        $pE16 = $this->pArray([1, $gap, 14, 15, 16, 17, 18, $gap, 20], 16);
        $pE17 = $this->pArray([1, $gap, 15, 16, 17, 18, 19, 20], 17);
        $pE18 = $this->pArray([1, $gap, 16, 17, 18, 19, 20], 18);
        $pE19 = $this->pArray([1, $gap, 16, 17, 18, 19, 20], 19);
        $pE20 = $this->pArray([1, $gap, 16, 17, 18, 19, 20], 20);
        
        $pSE1 = $this->pArray([1], 1);
        $pSE2 = $this->pArray([1, 2], 2);
        $pSE2b = $this->pArray([1, 2, 3], 2);
        $pSE3 = $this->pArray([1, 2, 3], 3);

        $this->assertSame($pE1, $this->p1->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE2, $this->p2->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE3, $this->p3->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE4, $this->p4->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE5, $this->p5->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE16, $this->p16->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE17, $this->p17->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE18, $this->p18->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE19, $this->p19->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE20, $this->p20->toArray($outerRange, $innerRange, $gap));
        
        $this->assertSame($pSE1, $this->pS1->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2, $this->pS2->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2b, $this->pS2b->toArray($outerRange, $innerRange));
        $this->assertSame($pSE3, $this->pS3->toArray($outerRange, $innerRange));
    }
    
    public function testToArrayExample2()
    {
        $outerRange = 2; 
        $innerRange = 2;
        $gap = null;
        
        $pE1 = $this->pArray([1, 2, 3, 4, 5, $gap, 19, 20], 1);
        $pE2 = $this->pArray([1, 2, 3, 4, 5, $gap, 19, 20], 2);
        $pE3 = $this->pArray([1, 2, 3, 4, 5, $gap, 19, 20], 3);
        $pE4 = $this->pArray([1, 2, 3, 4, 5, 6, $gap, 19, 20], 4);
        $pE5 = $this->pArray([1, 2, 3, 4, 5, 6, 7, $gap, 19, 20], 5);
        $pE6 = $this->pArray([1, 2, $gap, 4, 5, 6, 7, 8, $gap, 19, 20], 6);
        $pE15 = $this->pArray([1, 2, $gap, 13, 14, 15, 16, 17, $gap, 19, 20], 15);
        $pE16 = $this->pArray([1, 2, $gap, 14, 15, 16, 17, 18, 19, 20], 16);
        $pE17 = $this->pArray([1, 2, $gap, 15, 16, 17, 18, 19, 20], 17);
        $pE18 = $this->pArray([1, 2, $gap, 16, 17, 18, 19, 20], 18);
        $pE19 = $this->pArray([1, 2, $gap, 16, 17, 18, 19, 20], 19);
        $pE20 = $this->pArray([1, 2, $gap, 16, 17, 18, 19, 20], 20);

        $pSE1 = $this->pArray([1], 1);
        $pSE2 = $this->pArray([1, 2], 2);
        $pSE2b = $this->pArray([1, 2, 3], 2);
        $pSE3 = $this->pArray([1, 2, 3], 3);
        
        $this->assertSame($pE1, $this->p1->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE2, $this->p2->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE3, $this->p3->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE4, $this->p4->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE5, $this->p5->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE6, $this->p6->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE15, $this->p15->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE16, $this->p16->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE17, $this->p17->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE18, $this->p18->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE19, $this->p19->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE20, $this->p20->toArray($outerRange, $innerRange, $gap));
        
        $this->assertSame($pSE1, $this->pS1->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2, $this->pS2->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2b, $this->pS2b->toArray($outerRange, $innerRange));
        $this->assertSame($pSE3, $this->pS3->toArray($outerRange, $innerRange));
    }
    
    public function testToArrayExample3()
    {
        $outerRange = 0; 
        $innerRange = 2;
        $gap = null;
        
        $pE1 = $this->pArray([1, 2, 3, 4, 5], 1);
        $pE2 = $this->pArray([1, 2, 3, 4, 5], 2);
        $pE3 = $this->pArray([1, 2, 3, 4, 5], 3);
        $pE4 = $this->pArray([2, 3, 4, 5, 6], 4);
        $pE5 = $this->pArray([3, 4, 5, 6, 7], 5);
        $pE16 = $this->pArray([14, 15, 16, 17, 18], 16);
        $pE17 = $this->pArray([15, 16, 17, 18, 19], 17);
        $pE18 = $this->pArray([16, 17, 18, 19, 20], 18);
        $pE19 = $this->pArray([16, 17, 18, 19, 20], 19);
        $pE20 = $this->pArray([16, 17, 18, 19, 20], 20);

        $pSE1 = $this->pArray([1], 1);
        $pSE2 = $this->pArray([1, 2], 2);
        $pSE2b = $this->pArray([1, 2, 3], 2);
        $pSE3 = $this->pArray([1, 2, 3], 3);
        
        $this->assertSame($pE1, $this->p1->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE2, $this->p2->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE3, $this->p3->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE4, $this->p4->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE5, $this->p5->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE16, $this->p16->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE17, $this->p17->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE18, $this->p18->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE19, $this->p19->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE20, $this->p20->toArray($outerRange, $innerRange, $gap));
        
        $this->assertSame($pSE1, $this->pS1->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2, $this->pS2->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2b, $this->pS2b->toArray($outerRange, $innerRange));
        $this->assertSame($pSE3, $this->pS3->toArray($outerRange, $innerRange));
    }
    
    public function testToArrayExample4()
    {
        $outerRange = 1; 
        $innerRange = 0;
        $gap = null;
        
        $pE1 = $this->pArray([1, $gap, 20], 1);
        $pE2 = $this->pArray([1, 2, $gap, 20], 2);
        $pE3 = $this->pArray([1, $gap, 3, $gap, 20], 3);
        $pE18 = $this->pArray([1, $gap, 18, $gap, 20], 18);
        $pE19 = $this->pArray([1, $gap, 19, 20], 19);
        $pE20 = $this->pArray([1, $gap, 20], 20);
        
        $pSE1 = $this->pArray([1], 1);
        $pSE2 = $this->pArray([1, 2], 2);
        $pSE2b = $this->pArray([1, 2, 3], 2);
        $pSE3 = $this->pArray([1, $gap, 3], 3);

        $this->assertSame($pE1, $this->p1->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE2, $this->p2->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE3, $this->p3->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE18, $this->p18->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE19, $this->p19->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE20, $this->p20->toArray($outerRange, $innerRange, $gap));
        
        $this->assertSame($pSE1, $this->pS1->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2, $this->pS2->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2b, $this->pS2b->toArray($outerRange, $innerRange));
        $this->assertSame($pSE3, $this->pS3->toArray($outerRange, $innerRange, $gap));
    }
    
    public function testToArrayExample5()
    {
        $outerRange = 0; 
        $innerRange = 0;
        $gap = null;
        
        $pE1 = $this->pArray([1], 1);
        $pE2 = $this->pArray([2], 2);
        $pE3 = $this->pArray([3], 3);
        $pE18 = $this->pArray([18], 18);
        $pE19 = $this->pArray([19], 19);
        $pE20 = $this->pArray([20], 20);
        
        $pSE1 = $this->pArray([1], 1);
        $pSE2 = $this->pArray([2], 2);
        $pSE2b = $this->pArray([2], 2);
        $pSE3 = $this->pArray([3], 3);

        $this->assertSame($pE1, $this->p1->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE2, $this->p2->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE3, $this->p3->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE18, $this->p18->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE19, $this->p19->toArray($outerRange, $innerRange, $gap));
        $this->assertSame($pE20, $this->p20->toArray($outerRange, $innerRange, $gap));
        
        $this->assertSame($pSE1, $this->pS1->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2, $this->pS2->toArray($outerRange, $innerRange));
        $this->assertSame($pSE2b, $this->pS2b->toArray($outerRange, $innerRange));
        $this->assertSame($pSE3, $this->pS3->toArray($outerRange, $innerRange, $gap));
    }
}
