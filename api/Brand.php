<?php
class Brand {
    public ?int $id;
    public int $fipe_code;
    public string $name;
    public string $type;
    public ?array $raw;

    public function __construct(int $fipe_code, string $name, string $type = 'carros', ?array $raw = null, ?int $id = null) {
        $this->fipe_code = $fipe_code;
        $this->name = $name;
        $this->type = $type;
        $this->raw = $raw;
        $this->id = $id;
    }
}
