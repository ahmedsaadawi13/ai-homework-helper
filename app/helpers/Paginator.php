<?php
// FILE: /app/helpers/Paginator.php

/**
 * Pagination helper class
 * Handles pagination for list pages
 */
class Paginator
{
    private $totalItems;
    private $perPage;
    private $currentPage;
    private $totalPages;

    /**
     * Constructor
     *
     * @param int $totalItems Total number of items
     * @param int $perPage Items per page
     * @param int $currentPage Current page number
     */
    public function __construct($totalItems, $perPage = 20, $currentPage = 1)
    {
        $this->totalItems = $totalItems;
        $this->perPage = $perPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = ceil($totalItems / $perPage);
    }

    /**
     * Get offset for SQL query
     *
     * @return int
     */
    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * Get limit for SQL query
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->perPage;
    }

    /**
     * Get total pages
     *
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Get current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Check if there is a previous page
     *
     * @return bool
     */
    public function hasPreviousPage()
    {
        return $this->currentPage > 1;
    }

    /**
     * Check if there is a next page
     *
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Get previous page number
     *
     * @return int|null
     */
    public function getPreviousPage()
    {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : null;
    }

    /**
     * Get next page number
     *
     * @return int|null
     */
    public function getNextPage()
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }

    /**
     * Get page range for pagination links
     *
     * @param int $delta Number of pages to show on each side of current page
     * @return array
     */
    public function getPageRange($delta = 2)
    {
        $start = max(1, $this->currentPage - $delta);
        $end = min($this->totalPages, $this->currentPage + $delta);

        return range($start, $end);
    }

    /**
     * Render pagination HTML
     *
     * @param string $baseUrl Base URL for pagination links
     * @return string
     */
    public function render($baseUrl = '')
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination">';
        $html .= '<ul>';

        // Previous button
        if ($this->hasPreviousPage()) {
            $html .= '<li><a href="' . $baseUrl . '?page=' . $this->getPreviousPage() . '">Previous</a></li>';
        }

        // Page numbers
        foreach ($this->getPageRange() as $page) {
            $active = $page === $this->currentPage ? ' class="active"' : '';
            $html .= '<li' . $active . '><a href="' . $baseUrl . '?page=' . $page . '">' . $page . '</a></li>';
        }

        // Next button
        if ($this->hasNextPage()) {
            $html .= '<li><a href="' . $baseUrl . '?page=' . $this->getNextPage() . '">Next</a></li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}
