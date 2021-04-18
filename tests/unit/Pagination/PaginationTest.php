<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\UnitTests\Pagination;

use \SemelaPavel\Pagination\Pagination;
use \PHPUnit\Framework\TestCase;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Pagination\Pagination
 */
final class PaginationTest extends TestCase
{
    /**
     * Helper method.
     * 
     * @param array<int, int|null> $pagesNumbers
     * 
     * @return array<int, array{'page': int|null, 'isCurrent': bool}>
     */
    protected function pArray(array $pagesNumbers, int $currentPage): array
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
    public function testConstruct(): array
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
    public function paginationArrayEg1Provider(): array
    {
        return [
            [0, 1, 2, [1, 2, 3, 4, 5, null, 20], 1],
            [0, 1, 2, [1, 2, 3, 4, 5, null, 20], 2],
            [0, 1, 2, [1, 2, 3, 4, 5, null, 20], 3],
            [0, 1, 2, [1, 2, 3, 4, 5, 6, null, 20], 4],
            [0, 1, 2, [1, null, 3, 4, 5, 6, 7, null, 20], 5],
            [0, 1, 2, [1, null, 14, 15, 16, 17, 18, null, 20], 16],
            [0, 1, 2, [1, null, 15, 16, 17, 18, 19, 20], 17],
            [0, 1, 2, [1, null, 16, 17, 18, 19, 20], 18],
            [0, 1, 2, [1, null, 16, 17, 18, 19, 20], 19],
            [0, 1, 2, [1, null, 16, 17, 18, 19, 20], 20],
            [1, 1, 2, [1], 1],
            [2, 1, 2, [1, 2], 2],
            [3, 1, 2, [1, 2, 3], 2],
            [3, 1, 2, [1, 2, 3], 3]
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
    public function paginationArrayEg2Provider(): array
    {
        return [
            [0, 2, 2, [1, 2, 3, 4, 5, null, 19, 20], 1],
            [0, 2, 2, [1, 2, 3, 4, 5, null, 19, 20], 2],
            [0, 2, 2, [1, 2, 3, 4, 5, null, 19, 20], 3],
            [0, 2, 2, [1, 2, 3, 4, 5, 6, null, 19, 20], 4],
            [0, 2, 2, [1, 2, 3, 4, 5, 6, 7, null, 19, 20], 5],
            [0, 2, 2, [1, 2, null, 4, 5, 6, 7, 8, null, 19, 20], 6],
            [0, 2, 2, [1, 2, null, 13, 14, 15, 16, 17, null, 19, 20], 15],
            [0, 2, 2, [1, 2, null, 14, 15, 16, 17, 18, 19, 20], 16],
            [0, 2, 2, [1, 2, null, 15, 16, 17, 18, 19, 20], 17],
            [0, 2, 2, [1, 2, null, 16, 17, 18, 19, 20], 18],
            [0, 2, 2, [1, 2, null, 16, 17, 18, 19, 20], 19],
            [0, 2, 2, [1, 2, null, 16, 17, 18, 19, 20], 20],
            [1, 2, 2, [1], 1],
            [2, 2, 2, [1, 2], 2],
            [3, 2, 2, [1, 2, 3], 2],
            [3, 2, 2, [1, 2, 3], 3]
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
    public function paginationArrayEg3Provider(): array
    {
        return [
            [0, 0, 2, [1, 2, 3, 4, 5], 1],
            [0, 0, 2, [1, 2, 3, 4, 5], 2],
            [0, 0, 2, [1, 2, 3, 4, 5], 3],
            [0, 0, 2, [2, 3, 4, 5, 6], 4],
            [0, 0, 2, [3, 4, 5, 6, 7], 5],
            [0, 0, 2, [14, 15, 16, 17, 18], 16],
            [0, 0, 2, [15, 16, 17, 18, 19], 17],
            [0, 0, 2, [16, 17, 18, 19, 20], 18],
            [0, 0, 2, [16, 17, 18, 19, 20], 19],
            [0, 0, 2, [16, 17, 18, 19, 20], 20],
            [1, 0, 2, [1], 1],
            [2, 0, 2, [1, 2], 2],
            [3, 0, 2, [1, 2, 3], 2],
            [3, 0, 2, [1, 2, 3], 3]
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
    public function paginationArrayEg4Provider(): array
    {
        return [
            [0, 1, 0, [1, null, 20], 1],
            [0, 1, 0, [1, 2, null, 20], 2],
            [0, 1, 0, [1, null, 3, null, 20], 3],
            [0, 1, 0, [1, null, 18, null, 20], 18],
            [0, 1, 0, [1, null, 19, 20], 19],
            [0, 1, 0, [1, null, 20], 20],
            [1, 1, 0, [1], 1],
            [2, 1, 0, [1, 2], 2],
            [3, 1, 0, [1, 2, 3], 2],
            [3, 1, 0, [1, null, 3], 3]
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
    public function paginationArrayEg5Provider(): array
    {
        return [
            [0, 0, 0, [1], 1],
            [0, 0, 0, [2], 2],
            [0, 0, 0, [3], 3],
            [0, 0, 0, [18], 18],
            [0, 0, 0, [19], 19],
            [0, 0, 0, [20], 20],
            [1, 0, 0, [1], 1],
            [2, 0, 0, [2], 2],
            [3, 0, 0, [2], 2],
            [3, 0, 0, [3], 3]
        ];
    
    }
    
    /**
     * @dataProvider paginationArrayEg1Provider
     * @dataProvider paginationArrayEg2Provider
     * @dataProvider paginationArrayEg3Provider
     * @dataProvider paginationArrayEg4Provider
     * @dataProvider paginationArrayEg5Provider
     * @depends testConstruct
     * 
     * @param array<int, Pagination> $paginations
     */
    public function testToArray(
        int $i,
        int $outerRange,
        int $innerRange,
        array $pages,
        int $currentPage,
        array $paginations): void
    {
        $pages = $this->pArray($pages, $currentPage);
        $paginations[$i]->setCurrentPage($currentPage);
        
        $this->assertSame($pages, $paginations[$i]->toArray($outerRange, $innerRange));
    }
}
