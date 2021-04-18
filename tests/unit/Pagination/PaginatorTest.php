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

use \SemelaPavel\Pagination\Paginator;
use \PHPUnit\Framework\TestCase;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Pagination\Paginator
 */
final class PaginatorTest extends TestCase
{
    protected array $paginators = [];
    
    protected function setUp(): void
    {
        // $numOfItems, $itemsPerPage, $currentPage
        $this->paginators = [
            0 => new Paginator(-1, -1, -1),
            1 => new Paginator(0, 0, 0),
            2 => new Paginator(null, 0, 0),
            3 => new Paginator(1, 1, 1),
            4 => new Paginator(1, 2, 2),
            5 => new Paginator(100, 15, 7),
            6 => new Paginator(0, 10, 10),
            7 => new Paginator(100, 15, 6)
        ];
    }

    /**
     * [$this->paginators index, numOfItems]
     */
    public function numOfItemsPovider(): array
    {
        return [[0, 0], [1, 0], [2, 0], [3, 1], [4, 1], [5, 100], [6, 0], [7, 100]];
    }
    
    /**
     * [$this->paginators index, itemsPerPage]
     */
    public function itemsPerPagePovider(): array
    {
        return [[0, 1], [1, 1], [2, 1], [3, 1], [4, 2], [5, 15], [6, 10], [7, 15]];
    }
    
    /**
     * [$this->paginators index, currentPage]
     */
    public function currentPagePovider(): array
    {
        return [[0, 1], [1, 1], [2, 1], [3, 1], [4, 1], [5, 7], [6, 1], [7, 6]];
    }
    
    /**
     * [$this->paginators index, numOfPages]
     */
    public function numOfPagesPovider(): array
    {
        return [[0, 1], [1, 1], [2, 1], [3, 1], [4, 1], [5, 7], [6, 1], [7, 7]];
    }
    
    /**
     * [$this->paginators index, currentPageLength]
     */
    public function currentPageLengthProvider(): array
    {
        return [[0, 0], [1, 0], [2, 0], [3, 1], [4, 1], [5, 10], [6, 0], [7, 15]];
    }
    
    /**
     * [$this->paginators index, offset]
     */
    public function offsetProvider(): array
    {
        return [[0, 0], [1, 0], [2, 0], [3, 0], [4, 0], [5, 90], [6, 0], [7, 75]];
    }
    
    /**
     * [$this->paginators index, firstPage]
     */
    public function firstPageProvider(): array
    {
        return [[0, 1], [1, 1], [2, 1], [3, 1], [4, 1], [5, 1], [6, 1], [7, 1]];
    }
    
    /**
     * [$this->paginators index, lastPage]
     */
    public function lastPageProvider(): array
    {
        return [[0, 1], [1, 1], [2, 1], [3, 1], [4, 1], [5, 7], [6, 1], [7, 7]];
    }
    
    /**
     * [$this->paginators index, isFirstPage result]
     */
    public function isFirstPageProvider(): array
    {
        return [
            [0, true], [1, true], [2, true], [3, true], [4, true], [5, false], [6, true], [7, false]
        ];
    }
    
    /**
     * [$this->paginators index, isLastPage result]
     */
    public function isLastPageProvider(): array
    {
        return [
            [0, true], [1, true], [2, true], [3, true], [4, true], [5, true], [6, true], [7, false]
        ];
    }
    
    /**
     * [$this->paginators index, nextPage result]
     */
    public function nextPageProvider(): array
    {
        return [
            [0, null], [1, null], [2, null], [3, null], [4, null], [5, null], [6, null], [7, 7]
        ];
    }
    
    /**
     * [$this->paginators index, prevPage result]
     */
    public function prevPageProvider(): array
    {
        return [
            [0, null], [1, null], [2, null], [3, null], [4, null], [5, 6], [6, null], [7, 5]
        ];
    }
    
    /**
     * [
     *     $this->paginators index,
     *     numOfItems to set,
     *     getNumOfItems(),
     *     getCurrentPage(),
     *     getNumOfPages(),
     *     getCurrentPageLength()
     * ]
     */
    public function setNumOfItemsProvider(): array
    {
        return [
            [0,   10, 10, 1, 10,  1],
            [1,   10, 10, 1, 10,  1],
            [2,   10, 10, 1, 10,  1],
            [3,    0,  0, 1,  1,  0],
            [4, null,  0, 1,  1,  0],
            [5,   25, 25, 2,  2, 10],
            [6,   10, 10, 1,  1, 10],
            [7,   -1,  0, 1,  1,  0]
        ];
    }
    
    /**
     * [
     *     $this->paginators index,
     *     itemsPerPage to set,
     *     getItemsPerPage(),
     *     getCurrentPage(),
     *     getNumOfPages(),
     *     getCurrentPageLength()
     * ]
     */
    public function setItemsPerPageProvider(): array
    {
        return [
            [0,   10, 10, 1,   1,  0],
            [1,   10, 10, 1,   1,  0],
            [2,   10, 10, 1,   1,  0],
            [3,    0,  1, 1,   1,  1],
            [4,  (int) 0.0,  1, 1,   1,  1],
            [5,   24, 24, 5,   5,  4],
            [6,    1,  1, 1,   1,  0],
            [7,   -1,  1, 6, 100,  1]
        ];
    }
    
    /**
     * [
     *     $this->paginators index,
     *     currentPage to set,
     *     getCurrentPage()
     * ]
     */
    public function setCurrentPageProvider(): array
    {
        return [
            [0, 10, 1],
            [1, -1, 1],
            [2, 10, 1],
            [3,  0, 1],
            [4, (int) '0', 1],
            [5, 6, 6],
            [6, 15, 1],
            [7, 8, 7]
        ];
    }

    /**
     * @dataProvider numOfItemsPovider
     */
    public function testGetNumOfItems(int $i, int $numOfItems): void
    {
        $this->assertSame($numOfItems, $this->paginators[$i]->getNumOfItems());
    }
    
    /**
     * @dataProvider itemsPerPagePovider
     */
    public function testGetItemsPerPage(int $i, int $itemsPerPage): void
    {
        $this->assertSame($itemsPerPage, $this->paginators[$i]->getItemsPerPage());
    }

    /**
     * @dataProvider currentPagePovider
     */
    public function testGetCurrentPage(int $i, int $currentPage): void
    {
        $this->assertSame($currentPage, $this->paginators[$i]->getCurrentPage());
    }
    
    /**
     * @dataProvider numOfPagesPovider
     */
    public function testGetNumOfPages(int $i, int $numOfPages): void
    {
        $this->assertSame($numOfPages, $this->paginators[$i]->getNumOfPages());
    }
    
    /**
     * @dataProvider currentPageLengthProvider
     */
    public function testGetCurrentPageLength(int $i, int $pageLength): void
    {
        $this->assertSame($pageLength, $this->paginators[$i]->getCurrentPageLength());
    }
    
    /**
     * @dataProvider offsetProvider
     */
    public function testGetOffset(int $i, int $offset): void
    {
        $this->assertSame($offset, $this->paginators[$i]->getOffset());
    }
    
    /**
     * @dataProvider firstPageProvider
     */
    public function testGetFirstPage(int $i, int $firstPage): void
    {
        $this->assertSame($firstPage, $this->paginators[$i]->getFirstPage());
    }
    
    /**
     * @dataProvider lastPageProvider
     */
    public function testGetLastPage(int $i, int $lastPage): void
    {
        $this->assertSame($lastPage, $this->paginators[$i]->getLastPage());
    }
    
    /**
     * @dataProvider isFirstPageProvider
     */
    public function testIsFirst(int $i, bool $isFirstPage): void
    {
        $this->assertSame($isFirstPage, $this->paginators[$i]->isFirst());
    }
    
    /**
     * @dataProvider isLastPageProvider
     */
    public function testIsLast(int $i, bool $isLastPage): void
    {
        $this->assertSame($isLastPage, $this->paginators[$i]->isLast());
    }
    
    /**
     * @dataProvider nextPageProvider
     */
    public function testGetNextPage(int $i, ?int $nextPage): void
    {
        $this->assertSame($nextPage, $this->paginators[$i]->getNextPage());
    }
    
    /**
     * @dataProvider prevPageProvider
     */
    public function testGetPrevPage(int $i, ?int $prevPage): void
    {
        $this->assertSame($prevPage, $this->paginators[$i]->getPrevPage());
    }
    
    /**
     * @dataProvider setNumOfItemsProvider
     */
    public function testSetNumOfItems(
        int $i,
        ?int $numOfItemsSet,
        int $numberOfItemsGet,
        int $currentPage,
        int $numOfPages,
        int $currentPageLength): void
    {
        $paginator = $this->paginators[$i]->setNumOfItems($numOfItemsSet);

        $this->assertSame($numberOfItemsGet, $paginator->getNumOfItems());
        $this->assertSame($currentPage, $paginator->getCurrentPage());
        $this->assertSame($numOfPages, $paginator->getNumOfPages());
        $this->assertSame($currentPageLength, $paginator->getCurrentPageLength());
    }
    
    /**
     * @dataProvider setItemsPerPageProvider
     */
    public function testSetItemsPerPage(
        int $i,
        int $itemsPerPageSet,
        int $itemsPerPageGet,
        int $currentPage,
        int $numOfPages,
        int $currentPageLength): void
    {
        $paginator = $this->paginators[$i]->setItemsPerPage($itemsPerPageSet);

        $this->assertSame($itemsPerPageGet, $paginator->getItemsPerPage());
        $this->assertSame($currentPage, $paginator->getCurrentPage());
        $this->assertSame($numOfPages, $paginator->getNumOfPages());
        $this->assertSame($currentPageLength, $paginator->getCurrentPageLength());
    }
    
    /**
     * @dataProvider setCurrentPageProvider
     */
    public function testSetCurrentPage(int $i, int $currentPageSet, int $currentPageGet): void
    {
        $paginator = $this->paginators[$i]->setCurrentPage($currentPageSet);

        $this->assertSame($currentPageGet, $paginator->getCurrentPage());
    }
}
