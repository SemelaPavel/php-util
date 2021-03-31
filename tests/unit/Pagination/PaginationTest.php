<?php declare (strict_types = 1);
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
 * 
 * @covers \SemelaPavel\Pagination\Pagination
 */
final class PaginationTest extends TestCase
{
    /**
     * Helper method.
     */
    protected function pArray($pagesNumbers, $currentPage)
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
    
    /**
     * Prepared Paginations for the test bellow. This test does not perform
     * any assertions itself, but must not cause any errors or outputs.
     * 
     * @doesNotPerformAssertions
     */
    public function testConstruct()
    {
        return [
            0 => new Pagination(100, 5, 1),
            1 => new Pagination(20, 20, 1),
            2 => new Pagination(20, 10, 1),
            3 => new Pagination(21, 7, 1)
        ];
    }
    
    /**
     * Example 1 data provider.
     * [
     *     index of Pagination object from testConstruct(),
     *     outer range,
     *     inner range,
     *     gap,
     *     array of pages,
     *     current page 
     * ]
     */
    public function paginationArrayEg1Provider()
    {
        return [
            'EG1:O1/I2' => [0, 1, 2, null, [1, 2, 3, 4, 5, null, 20], 1],
            'EG1:O1/I2' => [0, 1, 2, null, [1, 2, 3, 4, 5, null, 20], 2],
            'EG1:O1/I2' => [0, 1, 2, null, [1, 2, 3, 4, 5, null, 20], 3],
            'EG1:O1/I2' => [0, 1, 2, null, [1, 2, 3, 4, 5, 6, null, 20], 4],
            'EG1:O1/I2' => [0, 1, 2, null, [1, null, 3, 4, 5, 6, 7, null, 20], 5],
            'EG1:O1/I2' => [0, 1, 2, null, [1, null, 14, 15, 16, 17, 18, null, 20], 16],
            'EG1:O1/I2' => [0, 1, 2, null, [1, null, 15, 16, 17, 18, 19, 20], 17],
            'EG1:O1/I2' => [0, 1, 2, null, [1, null, 16, 17, 18, 19, 20], 18],
            'EG1:O1/I2' => [0, 1, 2, null, [1, null, 16, 17, 18, 19, 20], 19],
            'EG1:O1/I2' => [0, 1, 2, null, [1, null, 16, 17, 18, 19, 20], 20],
            'EG1:O1/I2' => [1, 1, 2, null, [1], 1],
            'EG1:O1/I2' => [2, 1, 2, null, [1, 2], 2],
            'EG1:O1/I2' => [3, 1, 2, null, [1, 2, 3], 2],
            'EG1:O1/I2' => [3, 1, 2, null, [1, 2, 3], 3]
        ];
    }
    
    /**
     * Example 2 data provider.
     * [
     *     index of Pagination object from testConstruct(),
     *     outer range,
     *     inner range,
     *     gap,
     *     array of pages,
     *     current page 
     * ]
     */
    public function paginationArrayEg2Provider()
    {
        return [
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, 3, 4, 5, null, 19, 20], 1],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, 3, 4, 5, null, 19, 20], 2],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, 3, 4, 5, null, 19, 20], 3],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, 3, 4, 5, 6, null, 19, 20], 4],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, 3, 4, 5, 6, 7, null, 19, 20], 5],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 4, 5, 6, 7, 8, null, 19, 20], 6],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 13, 14, 15, 16, 17, null, 19, 20], 15],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 14, 15, 16, 17, 18, 19, 20], 16],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 15, 16, 17, 18, 19, 20], 17],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 16, 17, 18, 19, 20], 18],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 16, 17, 18, 19, 20], 19],
            'EG2:O2/I2' => [0, 2, 2, null, [1, 2, null, 16, 17, 18, 19, 20], 20],
            'EG2:O2/I2' => [1, 2, 2, null, [1], 1],
            'EG2:O2/I2' => [2, 2, 2, null, [1, 2], 2],
            'EG2:O2/I2' => [3, 2, 2, null, [1, 2, 3], 2],
            'EG2:O2/I2' => [3, 2, 2, null, [1, 2, 3], 3]
        ];
    
    }
    
    /**
     * Example 3 data provider.
     * [
     *     index of Pagination object from testConstruct(),
     *     outer range,
     *     inner range,
     *     gap,
     *     array of pages,
     *     current page 
     * ]
     */
    public function paginationArrayEg3Provider()
    {
        return [
            'EG3:O0/I2' => [0, 0, 2, null, [1, 2, 3, 4, 5], 1, 1],
            'EG3:O0/I2' => [0, 0, 2, null, [1, 2, 3, 4, 5], 2],
            'EG3:O0/I2' => [0, 0, 2, null, [1, 2, 3, 4, 5], 3],
            'EG3:O0/I2' => [0, 0, 2, null, [2, 3, 4, 5, 6], 4],
            'EG3:O0/I2' => [0, 0, 2, null, [3, 4, 5, 6, 7], 5],
            'EG3:O0/I2' => [0, 0, 2, null, [14, 15, 16, 17, 18], 16],
            'EG3:O0/I2' => [0, 0, 2, null, [15, 16, 17, 18, 19], 17],
            'EG3:O0/I2' => [0, 0, 2, null, [16, 17, 18, 19, 20], 18],
            'EG3:O0/I2' => [0, 0, 2, null, [16, 17, 18, 19, 20], 19],
            'EG3:O0/I2' => [0, 0, 2, null, [16, 17, 18, 19, 20], 20],
            'EG3:O0/I2' => [1, 0, 2, null, [1], 1],
            'EG3:O0/I2' => [2, 0, 2, null, [1, 2], 2],
            'EG3:O0/I2' => [3, 0, 2, null, [1, 2, 3], 2],
            'EG3:O0/I2' => [3, 0, 2, null, [1, 2, 3], 3]
        ];
    
    }
    
    /**
     * Example 4 data provider.
     * [
     *     index of Pagination object from testConstruct(),
     *     outer range,
     *     inner range,
     *     gap,
     *     array of pages,
     *     current page 
     * ]
     */
    public function paginationArrayEg4Provider()
    {
        return [
            'EG4:O1/I0' => [0, 1, 0, null, [1, null, 20], 1],
            'EG4:O1/I0' => [0, 1, 0, null, [1, 2, null, 20], 2],
            'EG4:O1/I0' => [0, 1, 0, null, [1, null, 3, null, 20], 3],
            'EG4:O1/I0' => [0, 1, 0, null, [1, null, 18, null, 20], 18],
            'EG4:O1/I0' => [0, 1, 0, null, [1, null, 19, 20], 19],
            'EG4:O1/I0' => [0, 1, 0, null, [1, null, 20], 20],
            'EG4:O1/I0' => [1, 1, 0, null, [1], 1],
            'EG4:O1/I0' => [2, 1, 0, null, [1, 2], 2],
            'EG4:O1/I0' => [3, 1, 0, null, [1, 2, 3], 2],
            'EG4:O1/I0' => [3, 1, 0, null, [1, null, 3], 3]
        ];
    
    }
    
    /**
     * Example 5 data provider.
     * [
     *     index of Pagination object from testConstruct(),
     *     outer range,
     *     inner range,
     *     gap,
     *     array of pages,
     *     current page 
     * ]
     */
    public function paginationArrayEg5Provider()
    {
        return [
            'EG5:O0/I0' => [0, 0, 0, null, [1], 1],
            'EG5:O0/I0' => [0, 0, 0, null, [2], 2],
            'EG5:O0/I0' => [0, 0, 0, null, [3], 3],
            'EG5:O0/I0' => [0, 0, 0, null, [18], 18],
            'EG5:O0/I0' => [0, 0, 0, null, [19], 19],
            'EG5:O0/I0' => [0, 0, 0, null, [20], 20],
            'EG5:O0/I0' => [1, 0, 0, null, [1], 1],
            'EG5:O0/I0' => [2, 0, 0, null, [2], 2],
            'EG5:O0/I0' => [3, 0, 0, null, [2], 2],
            'EG5:O0/I0' => [3, 0, 0, null, [3], 3]
        ];
    
    }
    
    /**
     * @dataProvider paginationArrayEg1Provider
     * @dataProvider paginationArrayEg2Provider
     * @dataProvider paginationArrayEg3Provider
     * @dataProvider paginationArrayEg4Provider
     * @dataProvider paginationArrayEg5Provider
     * @depends testConstruct
     */
    public function testToArray($i, $outerRange, $innerRange, $gap, $pages, $currentPage, $paginations)
    {
        $pages = $this->pArray($pages, $currentPage);
        $paginations[$i]->setCurrentPage($currentPage);
        
        $this->assertSame($pages, $paginations[$i]->toArray($outerRange, $innerRange, $gap));
    }
}
