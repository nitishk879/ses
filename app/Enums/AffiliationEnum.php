<?php

namespace App\Enums;

enum AffiliationEnum: int
{
    case COMPANY_EMPLOYEE = 1;
    case PARTNER_COMPANY_EMPLOYEE = 2;
    case  PARTNER_COMPANY_EMPLOYEE_PLUS= 3;
    case  FREELANCER= 4;
    // case  FREELANCER_SINGLE= 5;
    // case  FREELANCER_MORE= 6;


    /**
     * @throws \Exception
     */
    public static function toName(int $value): string
    {
        // TODO: Implement cases() method.
        return match ($value) {
            self::COMPANY_EMPLOYEE->value => __("projects/form.COMPANY_EMPLOYEE") ?? "Company employees",
            self::PARTNER_COMPANY_EMPLOYEE->value => __("projects/form.PARTNER_COMPANY_EMPLOYEE") ?? "Partner company employees",
            self::FREELANCER->value => __("projects/form.FREELANCER") ?? "Freelancer",
            self::PARTNER_COMPANY_EMPLOYEE_PLUS->value => __("projects/form.PARTNER_COMPANY_EMPLOYEE_PLUS") ?? "Self Employed",
            default => throw new \Exception('Unexpected match value'),
//            self::FREELANCER_MORE => __("projects/form.FREELANCER_MORE") ?? "Freelance: Two companies or more",
//            self::FREELANCER_SINGLE => __("projects/form.FREELANCER_SINGLE") ?? "Freelance One company",
        };
    }
}
