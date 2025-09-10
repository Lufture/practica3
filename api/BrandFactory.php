<?php
class BrandFactory {
    // $data is one element from the API array (expects keys: nome, codigo)
    public static function fromApi(array $data, string $type = 'carros'): Brand {
        $code = isset($data['codigo']) ? (int)$data['codigo'] : (int)$data['codigoMarca'] ?? 0;
        $name = $data['nome'] ?? $data['marca'] ?? '';
        return new Brand($code, $name, $type, $data);
    }
}
