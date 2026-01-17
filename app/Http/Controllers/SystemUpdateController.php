<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemUpdateController extends Controller
{
    /**
     * Display the system update page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $version = null;
        $versionFile = base_path('version.json');
        
        if (file_exists($versionFile)) {
            $version = json_decode(file_get_contents($versionFile));
        }
        
        return view('pengaturan.system_update.index', compact('version'));
    }
    
    /**
     * Check for updates via AJAX
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
        
        return response()->json([
            'currentVersion' => $currentVersion['version'] ?? 'N/A',
            'latestVersion' => $currentVersion['version'] ?? 'N/A',
            'updateAvailable' => false,
            'message' => 'Anda menggunakan versi terbaru'
        ]);
    }
}
