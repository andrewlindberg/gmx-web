<?php
namespace GameX\Core\Pagination;

use \Illuminate\Database\Eloquent\Collection;
use \Slim\Http\Request;
use \Psr\Http\Message\UriInterface;

class Pagination {
    const COUNT_PER_PAGE = 20;

    /**
     * @var UriInterface
     */
    protected $uri;
    protected $count = 0;
    protected $page = 0;
    protected $pagesCount = 0;
    protected $startPage = 0;
    protected $endPage = 0;

    protected $collection;

    public function __construct(Collection $collection, Request $request) {
        $this->uri = $request->getUri();
        $this->count = $collection->count();
        $this->page = ($request->getQueryParam('page') ?: 1) - 1;

        $this->collection = $collection->forPage($this->page, self::COUNT_PER_PAGE);

        $this->pagesCount = ceil($this->count / self::COUNT_PER_PAGE);
        $this->startPage = $this->page - 5;
        $this->endPage = $this->page + 5;

        if ($this->startPage < 0) {
            $this->startPage = 0;
        }

        if ($this->endPage > $this->pagesCount) {
            $this->endPage = $this->pagesCount;
        }
    }

    public function getCount() {
        return $this->count;
    }

    public function getPage() {
        return $this->page;
    }

    public function getPagesCount() {
        return $this->pagesCount;
    }

    public function getCountPerPage() {
        return self::COUNT_PER_PAGE;
    }

    public function getStartPage() {
        return $this->startPage;
    }

    public function getEndPage() {
        return $this->endPage;
    }

    public function getCollection() {
        return $this->collection;
    }

    public function getPageUrl($page) {
        parse_str($this->uri->getQuery(), $query);
        if ($page != 1) {
            $query['page'] = $page;
        } elseif (array_key_exists('page', $query)) {
            unset($query['page']);
        }

        return $this->uri->getBasePath() . '/' . ltrim($this->uri->getPath(), '/')
            . '?' . http_build_query($query);
    }
}
