<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Enums\App;
use App\Enums\Date;
use App\Enums\Timezone;
use App\Models\AppSettings;

class CompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if($user) {
            $companyId = $user->company_id ?? App::APP_SETTINGS_RECORD_ID->value;

            $company = Company::find($companyId);
            $appSettings = AppSettings::where('company_id', $companyId)->first();

            $isAdminRole = $user->hasRole('Admin');
            $timezone = $company?->timezone ?? Timezone::APP_DEFAULT_TIME_ZONE->value;
            $dateFormat = $company?->date_format ?? Date::APP_DEFAULT_DATE_FORMAT->value;
            $timeFormat = $company?->time_format ?? App::APP_DEFAULT_TIME_FORMAT->value;
            $activeSmsApi = $company?->active_sms_api;
            $isEnableCrm = $company?->is_enable_crm;

            $siteDetail = [
                'id' => $appSettings ? $appSettings->id : null,
                'name' => $appSettings ? $appSettings->application_name : null,
                'colored_logo' => $appSettings ? $appSettings->colored_logo : null,
            ];

            $companyDetail = [
                'id' => $company?->id,
                'name' => $company?->name ?? '',
                'email' => $company?->email ?? '',
                'mobile' => $company?->mobile ?? '',
                'address' => $company?->address ?? '',
                'tax_number' => $company?->tax_number ?? '',
                'timezone' => $timezone,
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'active_sms_api' => $activeSmsApi,
                'number_precision' => $company?->number_precision,
                'quantity_precision' => $company?->quantity_precision,

                'show_sku' => $company?->show_sku,
                'show_mrp' => $company?->show_mrp,
                'restrict_to_sell_above_mrp'=> $company?->restrict_to_sell_above_mrp,
                'restrict_to_sell_below_msp'=> $company?->restrict_to_sell_below_msp,
                'auto_update_sale_price'=> $company?->auto_update_sale_price,
                'auto_update_purchase_price'=> $company?->auto_update_purchase_price,
                'auto_update_average_purchase_price'=> $company?->auto_update_average_purchase_price,

                'is_item_name_unique' => $company?->is_item_name_unique,
                'tax_type' => $company?->tax_type,

                'enable_serial_tracking' => $company?->enable_serial_tracking,
                'enable_batch_tracking' => $company?->enable_batch_tracking,
                'is_batch_compulsory' => $company?->is_batch_compulsory,
                'enable_mfg_date' => $company?->enable_mfg_date,
                'enable_exp_date' => $company?->enable_exp_date,
                'enable_color' => $company?->enable_color,
                'enable_size' => $company?->enable_size,
                'enable_model' => $company?->enable_model,

                'show_tax_summary' => $company?->show_tax_summary,
                'state_id' => $company?->state_id,
                'terms_and_conditions' => $company?->terms_and_conditions,
                'show_terms_and_conditions_on_invoice' => $company?->show_terms_and_conditions_on_invoice,
                'show_party_due_payment' => $company?->show_party_due_payment,
                'bank_details' => $company?->bank_details,
                'signature' => $company?->signature,
                'show_signature_on_invoice' => $company?->show_signature_on_invoice,
                'show_brand_on_invoice' => $company?->show_brand_on_invoice,
                'show_tax_number_on_invoice' => $company?->show_tax_number_on_invoice,
                'colored_logo' => $company?->colored_logo,

                'is_enable_crm' => $isEnableCrm,
                'is_enable_carrier' => $company?->is_enable_carrier,
                'is_enable_carrier_charge'  => $company?->is_enable_carrier_charge,
                'show_discount' => $company?->show_discount,
                'allow_negative_stock_billing' => $company?->allow_negative_stock_billing,
                'show_hsn' => $company?->show_hsn,
                'is_enable_secondary_currency' => $company?->is_enable_secondary_currency,
            ];

            app()->instance('company', $companyDetail);
            app()->instance('site', $siteDetail);
            app()->instance('isAdminRole', $isAdminRole);
        }

        return $next($request);
    }
}
