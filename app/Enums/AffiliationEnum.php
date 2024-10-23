<?php

namespace App\Enums;

enum AffiliationEnum: int
{
    case COMPANY_EMPLOYEE = 1;
    case PARTNER_COMPANY_EMPLOYEE = 2;
    case  PARTNER_COMPANY_EMPLOYEE_PLUS= 3;
    case  FREELANCER= 4;
    case  FREELANCER_SINGLE= 5;
    case  FREELANCER_MORE= 6;


    public static function toName($value): string
    {
        // TODO: Implement cases() method.
        return match ($value) {
            self::COMPANY_EMPLOYEE => __("projects/form.COMPANY_EMPLOYEE") ?? "Company employees",
            self::PARTNER_COMPANY_EMPLOYEE => __("projects/form.PARTNER_COMPANY_EMPLOYEE") ?? "Partner company employees",
            self::FREELANCER => __("projects/form.FREELANCER") ?? "Freelancer",
            self::FREELANCER_SINGLE => __("projects/form.FREELANCER_SINGLE") ?? "Freelance One company",
            self::PARTNER_COMPANY_EMPLOYEE_PLUS => __("projects/form.PARTNER_COMPANY_EMPLOYEE_PLUS") ?? "Partner company employees: 2 companies or more",
            self::FREELANCER_MORE => __("projects/form.FREELANCER_MORE") ?? "Freelance: Two companies or more",
        };
    }
}
