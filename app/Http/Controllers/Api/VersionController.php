<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    /**
     * Get application version info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $versionFile = base_path('version.json');
        
        if (!file_exists($versionFile)) {
            return \App\Support\ApiResponse::error('Version file not found', 404);
        }
        
        $version = json_decode(file_get_contents($versionFile), true);
        
        return \App\Support\ApiResponse::success($version);
    }
    
    /**
     * Check for updates (compare with remote)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdate(Request $request)
    {
        $versionFile = base_path('version.json');
        $currentVersion = null;
        
        if (file_exists($versionFile)) {
            $currentVersion = json_decode(file_get_contents($versionFile), true);
        }
        
        $clientVersion = $request->input('version', '0.0.0');
        $minVersion = $currentVersion['min_mobile_version'] ?? '1.0.0';
        $latestVersion = $currentVersion['mobile_version'] ?? ($currentVersion['version'] ?? '1.0.0');
        
        $updateAvailable = version_compare($clientVersion, $latestVersion, '<');
        $forceUpdate = version_compare($clientVersion, $minVersion, '<');

        return \App\Support\ApiResponse::success([
            'currentVersion' => $clientVersion,
            'latestVersion' => $latestVersion,
            'minVersion' => $minVersion,
            'updateAvailable' => $updateAvailable,
            'forceUpdate' => $forceUpdate,
            'message' => $updateAvailable ? 'Update is available' : 'You are running the latest version'
        ]);
    }
}
