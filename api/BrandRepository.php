<?php
class BrandRepository implements BrandRepositoryInterface {
    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(Brand $brand): int {
        // upsert by unique fipe_code+type
        $sql = "INSERT INTO brands (fipe_code, name, type, raw_json)
                VALUES (:fipe_code, :name, :type, :raw_json)
                ON DUPLICATE KEY UPDATE name = VALUES(name), raw_json = VALUES(raw_json), updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fipe_code' => $brand->fipe_code,
            ':name' => $brand->name,
            ':type' => $brand->type,
            ':raw_json' => $brand->raw ? json_encode($brand->raw, JSON_UNESCAPED_UNICODE) : null
        ]);
        // return id (if new), else fetch id
        $id = (int)$this->pdo->lastInsertId();
        if ($id === 0) {
            // row existed: fetch id
            $row = $this->pdo->prepare("SELECT id FROM brands WHERE fipe_code = :code AND type = :type");
            $row->execute([':code'=>$brand->fipe_code, ':type'=>$brand->type]);
            $r = $row->fetch(PDO::FETCH_ASSOC);
            return (int)$r['id'];
        }
        return $id;
    }

    public function findAll(int $limit = 100): array {
        $stmt = $this->pdo->prepare("SELECT id, fipe_code, name, type, raw_json FROM brands ORDER BY name LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $raw = $r['raw_json'] ? json_decode($r['raw_json'], true) : null;
            $result[] = new Brand((int)$r['fipe_code'], $r['name'], $r['type'], $raw, (int)$r['id']);
        }
        return $result;
    }

    public function findByFipeCode(int $fipeCode, string $type = 'carros'): ?Brand {
        $stmt = $this->pdo->prepare("SELECT id, fipe_code, name, type, raw_json FROM brands WHERE fipe_code = :code AND type = :type LIMIT 1");
        $stmt->execute([':code'=>$fipeCode, ':type'=>$type]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        $raw = $r['raw_json'] ? json_decode($r['raw_json'], true) : null;
        return new Brand((int)$r['fipe_code'], $r['name'], $r['type'], $raw, (int)$r['id']);
    }
}
