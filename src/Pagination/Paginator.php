<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Pagination;

/**
 * Simple pagination calculator to help with calculate number of pages
 * for specific number of items, number of items per page, offset etc.
 * The class also holds current page number, can calculate next page or previous
 * page and you can easily get first page or last page too.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class Paginator
{
    const FIRST_PAGE = 1;
    const ITEMS_PER_PAGE_MIN = 1;
    const NUM_OF_PAGES_MIN = 1;
    
    protected int $numOfItems;
    protected int $itemsPerPage;
    protected int $currentPage;
    protected int $numOfPages = self::NUM_OF_PAGES_MIN;
    
    /**
     * Returns new paginator object with specific number of items,
     * the maximum number of items per page and the current page.
     * The total number of pages is updated during initialization too.
     * 
     * @param int|null $numOfItems Total number of items to paginate.
     * @param int $itemsPerPage Maximum number of items to show per one page.
     * @param int $currentPage The number of the current page.
     */
    public function __construct(?int $numOfItems, int $itemsPerPage, int $currentPage)
    {
        $this->setNumOfItems($numOfItems);
        $this->setItemsPerPage($itemsPerPage);
        $this->setCurrentPage($currentPage);
    }
    
    /**
     * @return int Total number of items to paginate.
     */
    public function getNumOfItems(): int
    {
        return $this->numOfItems;
    }
    
    /**
     * Returns the set number of items to show per one page.
     * 
     * @return int Number of items per one page.
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
    
    /**
     * Returns the number which points to the current page.
     * 
     * @return int The number of the current page.
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Return the total number of pages calculated from number
     * of items and items per page.
     * 
     * @return int The total number of pages.
     */
    public function getNumOfPages(): int
    {
        return $this->numOfPages;
    }
 
    /**
     * Returns the number of current page items. Last page may have
     * different number of items than other pages.
     * 
     * @return int The number of current page items.
     */
    public function getCurrentPageLength(): int
    {
        if ($this->isLast()) {
            return $this->numOfItems - ($this->itemsPerPage * ($this->currentPage - 1));
        }
        
        return $this->itemsPerPage;
    }
    
    /**
     * The offset is tipicaly used to identify the starting point (excluded)
     * to return rows from database. Basically, it exclude the first
     * set of records (items). e.g. offset 10 means that you want items
     * from 11 (included).
     * 
     * @return int Current page offset.
     */
    public function getOffset(): int
    {
        return $this->itemsPerPage * ($this->currentPage - 1);
    }
    
    /**
     * @return int The first page number.
     */
    public function getFirstPage(): int
    {
        return self::FIRST_PAGE;
    }
    
    /**
     * @return int The last page number.
     */
    public function getLastPage(): int
    {
        return $this->numOfPages;
    }
    
    /**
     * @return bool True if the current page is the first page, false otherwise.
     */
    public function isFirst(): bool
    {
        return $this->currentPage == self::FIRST_PAGE;
    }
    
    /**
     * @return bool True if the current page is the last page, false otherwise.
     */
    public function isLast(): bool
    {
        return $this->numOfItems == 0 ? true : $this->currentPage == $this->numOfPages;
    }

    /**
     * @return int|null Number of next page, or null if there is no next page.
     */
    public function getNextPage(): ?int
    {
        $nextPage = $this->currentPage + 1;
        
        return $nextPage > $this->getLastPage() ? null : $nextPage;
    }
    
    /**
     * 
     * @return int|null Number of previous page, or null if there is no previous page.
     */
    public function getPrevPage(): ?int
    {
        $prevPage = $this->currentPage - 1;
        
        return $prevPage < $this->getFirstPage() ? null : $prevPage;
    }
    
    /**
     * Sets the total number of items and recalculate total number of pages.
     * 
     * @param int|null $numOfItems Total number of items to paginate.
     * 
     * @return self This instance for methods chaining.
     */
    public function setNumOfItems(?int $numOfItems): self
    {
        $this->numOfItems = max(0, (int) $numOfItems);
        $this->initNumOfPages();
        
        return $this;
    }
    
    /**
     * Sets the number of items per page and recalculate total number of pages.
     * Minimum number of items per page is preset by ITEMS_PER_PAGE_MIN constant.
     * Maximum number of items is not limited.
     * 
     * @param int $itemsPerPage Maximum number of items to show per one page.
     * 
     * @return self This instance for methods chaining.
     */
    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = max(self::ITEMS_PER_PAGE_MIN, $itemsPerPage);
        $this->initNumOfPages();
        
        return $this;
    }
    
    /**
     * The number of current page is set to nearest valid number. That means,
     * the current number cant be lower than first page number or higher
     * than number of total pages.
     * 
     * @param int $page The number of the current page.
     * 
     * @return self This instance for methods chaining.
     */
    public function setCurrentPage(int $page): self
    {
        if ($this->numOfItems == 0) {
            $this->currentPage = self::FIRST_PAGE;
        } else {
            $this->currentPage = min(max(self::FIRST_PAGE, $page), $this->numOfPages);
        }
        
        return $this;
    }

    /**
     * Initialises the number of total pages. Minimum number of pages
     * is preset by NUM_OF_PAGES_MIN constant.
     * Maximum number of pages is not limited.
     */
    protected function initNumOfPages(): void
    {
        if (isset($this->numOfItems) && isset($this->itemsPerPage)) {
            $this->numOfPages = max(
                self::NUM_OF_PAGES_MIN,
                (int) ceil($this->numOfItems / $this->itemsPerPage)
            );
            if (isset($this->currentPage)) {
                $this->setCurrentPage($this->currentPage);
            }
        }
    }
}
