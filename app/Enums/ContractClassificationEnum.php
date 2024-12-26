<?php

namespace App\Enums;

enum ContractClassificationEnum: string
{
    /**
     * https://gist.github.com/domthomas-dev/78e1db6da82512b716b0f8cac6dd93e7#file-localizedenum-php
     *
     * example for Enum
    */
//    case OUTSOURCING = 'quasi_delegation_possible';  //    'Business outsourcing (quasi-entrustment)';
    case OUTSOURCING_CONTRACT = 'available_for_contract'; //    'Business outsourcing (contract)';
    case DISPATCH_CONTRACT = 'available_for_dispatch'; //    'Dispatch contract';

    /**
     * Let's set name|title for the Enum
     *
     * @param $value
     * @return string
     */
    public static function toName($value): string
    {
        return match ($value) {
//            self::OUTSOURCING => __("talents/index.quasi_delegation_possible"),
            self::OUTSOURCING_CONTRACT => __("talents/index.available_for_contract"),
            self::DISPATCH_CONTRACT => __("talents/index.available_for_dispatch"),
        };
    }

    public static function label(): array
    {
        return array_reduce(self::cases(), function ($carry, ContractClassificationEnum $item) {
            $carry[$item->value] = $item->toName($item->name);
            return $carry;
        }, []);
    }
}
