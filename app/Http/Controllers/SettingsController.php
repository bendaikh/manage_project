<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        $groupedSettings = $settings->groupBy('group');
        
        return view('settings.index', compact('groupedSettings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required|string|max:255',
            'delivery_price' => 'required|numeric|min:0',
            'seller_delivery_price' => 'required|numeric|min:0',
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update text settings
            Setting::setValue('country', $request->country, 'string', 'delivery', 'Country name for delivery and billing');
            Setting::setValue('delivery_price', $request->delivery_price, 'number', 'delivery', 'Standard delivery price in local currency');
            Setting::setValue('seller_delivery_price', $request->seller_delivery_price, 'number', 'delivery', 'Seller-specific delivery price');
            Setting::setValue('app_name', $request->app_name, 'string', 'general', 'Application name');
            Setting::setValue('app_description', $request->app_description, 'string', 'general', 'Application description');

            // Handle logo upload
            if ($request->hasFile('app_logo')) {
                $logo = $request->file('app_logo');
                
                // Delete old logo if exists
                $oldLogo = Setting::getValue('app_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                // Store new logo
                $logoPath = $logo->store('logos', 'public');
                Setting::setValue('app_logo', $logoPath, 'file', 'appearance', 'Application logo displayed in sidebar and headers');
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSettings()
    {
        $settings = [
            'app_logo' => Setting::getLogoUrl(),
            'country' => Setting::getCountry(),
            'delivery_price' => Setting::getDeliveryPrice(),
            'seller_delivery_price' => Setting::getSellerDeliveryPrice(),
            'app_name' => Setting::getValue('app_name', 'Laravel App'),
            'app_description' => Setting::getValue('app_description', ''),
        ];

        return response()->json($settings);
    }

    public function deleteLogo()
    {
        try {
            $logoPath = Setting::getValue('app_logo');
            
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            
            Setting::setValue('app_logo', null, 'file', 'appearance', 'Application logo displayed in sidebar and headers');

            return response()->json([
                'success' => true,
                'message' => 'Logo deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logo: ' . $e->getMessage()
            ], 500);
        }
    }
}
