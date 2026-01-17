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
            return response()->json([
                'version' => 'N/A',
                'releaseDate' => null,
                'changelog' => [],
                'error' => 'Version file not found'
            ], 404);
        }
        
        $version = json_decode(file_get_contents($versionFile), true);
        
        return response()->json($version);
    }
    
    /**
     * Check for updates (compare with remote)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdate()
    {
        $versionFile = base_path('version.json');
        $currentVersion = null;
        
        if (file_exists($versionFile)) {
            $currentVersion = json_decode(file_get_contents($versionFile), true);
        }
        
        // For now, just return current version
        // Future: Check GitHub releases API for newer version
        return response()->json([
            'currentVersion' => $currentVersion['version'] ?? 'N/A',
            'latestVersion' => $currentVersion['version'] ?? 'N/A',
            'updateAvailable' => false,
            'message' => 'You are running the latest version'
        ]);
    }
}
