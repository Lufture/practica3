<?php
interface BrandRepositoryInterface {
    public function save(Brand $brand): int;  // returns last insert id
    public function findAll(int $limit = 100): array;
    public function findByFipeCode(int $fipeCode, string $type = 'carros'): ?Brand;
}
