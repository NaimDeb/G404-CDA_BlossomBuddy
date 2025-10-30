<?php

namespace App\DTO;

class PlantDto
{

    public function __construct(
        public int $apiId,
        public string $commonName,
        public ?array $watering,
        public ?string $growthRate,
        public ?string $maintenance,
    ) {}

    public function toArray(): array
    {
        return [
            'api_id' => $this->apiId,
            'common_name' => $this->commonName,
            'watering_general_benchmark' => $this->watering,
            'growth_rate' => $this->growthRate,
            'maintenance' => $this->maintenance,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            apiId: $data['api_id'],
            commonName: $data['common_name'],
            watering: $data['watering_general_benchmark'] ?? null,
            growthRate: $data['growth_rate'] ?? null,
            maintenance: $data['maintenance'] ?? null,
        );
    }
}

    // Todo : Voir les trucs récups ici
    // private function filterPlantData(array $plantData): array
    // {
    //     return [
    //         'api_id' => $plantData['id'],
    //         'common_name' => $plantData['common_name'],
    //         'watering_general_benchmark' => $plantData['watering_general_benchmark'],
    //         'watering' => $plantData['watering'] ?? null,
    //         'flowers' => (bool)($plantData['flowers'] ?? false),
    //         'fruits' => (bool)($plantData['fruits'] ?? false),
    //         'leaf' => (bool)($plantData['leaf'] ?? false),
    //         'growth_rate' => $plantData['growth_rate'] ?? null,
    //         'maintenance' => $plantData['maintenance'] ?? null,
    //     ];
    // }