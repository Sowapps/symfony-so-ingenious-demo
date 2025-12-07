<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious;

/**
 * Update only with clone to work with doctrine update detection
 */
class QueryCriteria {

    public function __construct(
        /**
         * @var array<string, mixed>
         * @example ['year' => 2025, 'month' => 10]
         */
        private array $filters = [],
        /**
         * @var array<string, string>
         * @example ['publishDate' => 'DESC']
         */
        private array $orderBy = [],
        private ?int  $limit = null,
    ) {
    }

    /**
     * Integrat other criteria to build a new one.
     * Filters are merges, orderBy and limit are overloaded.
     * @param QueryCriteria $criteria
     * @return QueryCriteria
     */
    public function and(QueryCriteria $criteria): QueryCriteria {
        // $criteria has priority, because this is a more specific usage
        return new self(
            filters: [...$this->filters, ...$criteria->filters],
            orderBy: $criteria->orderBy ?: $this->orderBy,
            limit: $criteria->limit ?? $this->limit,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilters(): array {
        return $this->filters;
    }

    /**
     * @return array<string, string>
     */
    public function getOrderBy(): array {
        return $this->orderBy;
    }

    public function getLimit(): ?int {
        return $this->limit;
    }

    public static function fromArray(array $data): self {
        return new self(
            filters: $data['filters'] ?? [],
            orderBy: $data['orderBy'] ?? [],
            limit: $data['limit'] ?? null,
        );
    }

    public function toArray(): array {
        return [
            'filters' => $this->filters,
            'orderBy' => $this->orderBy,
            'limit'   => $this->limit,
        ];
    }

    public static function empty(): self {
        return new self();
    }

    public function withFilters(array $filters): self {
        $clone = clone $this;
        $clone->filters = $filters;

        return $clone;
    }

    public function withOrder(array $orderBy): self {
        $clone = clone $this;
        $clone->orderBy = $orderBy;

        return $clone;
    }

    public function withLimit(?int $limit): self {
        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }
}
