<?php

namespace App\Mappers;

use App\DTO\PlantDto;

class PlantMapper {

    /**
     * Mapper pour les données complètes d'une Plante (endpoint : details)
     */
    public static function fromDetailsApi(array $rawData){
        return new PlantDto(
            apiId: $rawData["apiId"],
            commonName: $rawData["common_name"] ??"",
            watering: [
                'value' => $raw['watering']['value'] ?? null,
                'unit' => $raw['watering']['unit'] ?? 'days',
            ],
            growthRate: $raw['growth_rate'] ?? null,
            maintenance: $raw['maintenance'] ?? null,
            );
    }

    /**
     * Mapper pour les données partielles d'une plante (par l'endpoint search ou species-list)
     * Todo : Checker ce que je recois exactement
     */
    public static function fromSearchApi(array $rawData){
        return new PlantDto(
            apiId: $rawData["id"],
            commonName: $raw['common_name'] ?? 'Unknown',
            watering: null,
            growthRate: null,
            maintenance: null,
        );
    }

}