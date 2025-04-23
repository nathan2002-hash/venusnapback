<?php

namespace App\Http\Controllers\Api;

use App\Models\Tax;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
{
    public function getTaxInformation(Request $request)
    {
        $user = $request->user();
        $countries = $this->getCountries();

        $taxInfo = Tax::where('account_id', $user->account->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'For legal compliance, we collect tax information for payouts exceeding $400/year.',
                'countries' => $countries,
                'can_edit' => true,
                'lock_reason' => "For legal compliance, we collect tax informatio",
                'requirements' => [
                    'individual' => 'SSN or ITIN for individuals',
                    'business' => 'EIN for businesses',
                ],
                'existing_data' => $taxInfo ? [
                    'tax_type' => $taxInfo->type,
                    'full_legal_name' => $taxInfo->legal_name,
                    'tax_id' => $taxInfo->tax_number,
                    'address' => $taxInfo->address,
                    'city' => $taxInfo->city,
                    'state' => $taxInfo->state,
                    'zip_code' => $taxInfo->zip,
                    'country' => $taxInfo->country,
                ] : null
            ]
        ]);
    }

    public function saveTaxInformation(Request $request)
    {
        // $validated = $request->validate([
        //     'tax_type' => 'required|in:individual,business',
        //     'full_legal_name' => 'required|string|max:255',
        //     'tax_id' => 'required|string|max:255',
        //     'address' => 'required|string|max:255',
        //     'city' => 'required|string|max:255',
        //     'state' => 'required|string|max:255',
        //     'zip_code' => 'required|string|max:255',
        //     'country' => 'required|string|max:255',
        // ]);

        $accountId = Auth::user()->account->id;

        $taxInfo = Tax::where('account_id', $accountId)->first();



        if ($taxInfo) {
            $tax = Tax::find($taxInfo->id);
            $tax->account_id = $accountId;
            $tax->type = $request->tax_type;
            $tax->legal_names = $request->full_legal_name;
            $tax->tax_number = $request->tax_id;
            $tax->address = $request->address;
            $tax->city = $request->city;
            $tax->state = $request->state;
            $tax->zip = $request->zip_code;
            $tax->country = $request->country;
            $tax->save();
        } else {
            $tax = new Tax();
            $tax->account_id = $accountId;
            $tax->type = $request->tax_type;
            $tax->legal_names = $request->full_legal_name;
            $tax->tax_number = $request->tax_id;
            $tax->address = $request->address;
            $tax->city = $request->city;
            $tax->state = $request->state;
            $tax->zip = $request->zip_code;
            $tax->country = $request->country;
            $tax->save();
        }
        return response()->json([
            'success' => true,
            'message' => 'Tax information saved successfully',
            'data' => $tax
        ]);
    }


    private function getCountries()
    {
        return [
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'ZM', 'name' => 'Zambia'],
            // Add more countries as needed
        ];
    }
}
