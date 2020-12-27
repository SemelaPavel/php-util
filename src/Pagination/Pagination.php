<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Pagination;

use SemelaPavel\Pagination\Paginator;

/**
 * Paginator class extension that adds method to get array of pages
 * for advanced pagination.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class Pagination extends Paginator
{
    /**
     * Returns array of pages. For example if we have 20 total pages,
     * current page 5 and outer range set to 1 and inner range set to 2, you
     * will get this array:
     *  [
     *      ['page' => 1, 'isCurrent => false],
     *      ['page' => null, 'isCurrent => false],
     *      ['page' => 3, 'isCurrent => false],
     *      ['page' => 4, 'isCurrent => false],
     *      ['page' => 5, 'isCurrent => true],
     *      ['page' => 6, 'isCurrent => false],
     *      ['page' => 7, 'isCurrent => false],
     *      ['page' => null, 'isCurrent => false],
     *      ['page' => 20, 'isCurrent => false]
     *  ]      
     * 
     * Outer range means how much pages makes pagination borders. If it is set
     * for example to number 2, there will be max 2 pages numbers on left and
     * 2 pages numbers on right side of pagination bar.
     * 
     * Inner range means how much pages should be on left and right side 
     * of the current page and these pages should be inside outer pages
     * set before by outer range. If the current page is too much on the left 
     * side of pagination bar, are exceeding inner pages moved from left side
     * of current page to its right side and conversely if current page is too
     * much on the right side of pagination bar.
     * 
     * Examples:
     * [1, 2, 3, 4, 5, null, 20] for current page 1 and other settings as above.
     * [1, 2, 3, 4, 5, null, 20] for current page 2 and other settings as above.
     * [1, 2, 3, 4, 5, null, 20] for current page 3 and other settings as above.
     * [1, 2, 3, 4, 5, 6 null, 20] for current page 4 and other settings as above.
     * 
     * Example for lower number of pages:
     * [1, 2] should be pagination for 2 total pages with other settings
     * as above.
     * 
     * @param int $outerRange Number of pages on left and right side of pagination bar.
     * @param int $innerRange Number of pages on left and right side of current page.
     * @param string $gap Non-contiguous pages separator.
     * 
     * @return array Pages numbers of pagination.
     */
    public function toArray($outerRange = 1, $innerRange = 2, $gap = null)
    {
        $pages = [];
        
        $outerLeftEnd = min($this->numOfPages, $outerRange);
        $outerRightStart = max($this->numOfPages - ($outerRange - 1), $outerLeftEnd + 1);
        $innerStart = $this->getInnerStartPage($innerRange, $outerLeftEnd);
        $innerEnd = $this->getInnerEndPage($innerRange, $outerRightStart);

        for ($i = self::FIRST_PAGE; $i <= $outerLeftEnd; $i++) {
            $pages[] = $this->getPage($i);
        }
        
        if ($outerLeftEnd < ($innerStart - 1) && self::FIRST_PAGE <= $outerLeftEnd) {
            $pages[] = $this->getPage($gap);
        }
        
        for ($i = $innerStart; $i <= $innerEnd; $i++) {
            $pages[] = $this->getPage($i);
        }
        
        if ($outerRightStart > ($innerEnd + 1) && $outerRightStart <= $this->numOfPages) {
            $pages[] = $this->getPage($gap);
        }
        
        for ($i = $outerRightStart; $i <= $this->numOfPages; $i++) {
            $pages[] = $this->getPage($i);
        }
        
        return $pages;
    }

    /**
     * Returns array of pair of page number and boolean true if the page
     * is also current page, or false if not.
     * 
     * @param int $page Page number.
     * 
     * @return array Pair of page number and current page boolean identifier.
     */
    protected function getPage($page)
    {
        return ['page' => $page, 'isCurrent' => ($page === $this->currentPage)];
    }       
    
    /**
     * Returns page number which determines where should inner pages
     * part of pagination starts.
     * 
     * @param int $innerRange Number of pages on left and right side of current page.
     * @param int $outerLeftEnd Ending page number of left outer pagination side.
     * 
     * @return int Page number where inner pages part of pagination starts.
     */
    protected function getInnerStartPage($innerRange, $outerLeftEnd)
    {
        if (($this->currentPage - $innerRange) <= $outerLeftEnd) {
            $innerStart = $outerLeftEnd + 1;
        } else {
            $innerStart = $this->currentPage - $innerRange;
        }
        
        if (($this->currentPage + $innerRange) > $this->numOfPages) {
            $innerStart -= (($this->currentPage + $innerRange) - $this->numOfPages);
        }
        
        return max($innerStart, $outerLeftEnd + 1);
    }
    
    /**
     * Returns page number which determines where should inner pages
     * part of pagination ends.
     * 
     * @param int $innerRange Number of pages on left and right side of current page.
     * @param int $outerRightStart First page number of right outer pagination side.
     * 
     * @return int Page number where inner pages part of pagination ends.
     */
    protected function getInnerEndPage($innerRange, $outerRightStart)
    {
        if (($this->currentPage + $innerRange) >= $outerRightStart) {
            $innerEnd = $outerRightStart - 1;
        } else {
            $innerEnd = $this->currentPage + $innerRange;
        }
        
        if (($this->currentPage - $innerRange) < self::FIRST_PAGE) {
            $innerEnd += (1 + abs($this->currentPage - $innerRange));
        }
        
        return min($innerEnd, $outerRightStart - 1);
    }
}
