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
         * @var array<string, mixed>
         * @example [['publishDate', 'DESC']]
         */
        private array $orderBy = [],
        private ?int  $limit = null,
    ) {
    }

    public function getFilters(): array {
        return $this->filters;
    }

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
