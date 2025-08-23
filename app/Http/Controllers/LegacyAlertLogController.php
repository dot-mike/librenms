<?php

namespace App\Http\Controllers;

use App\Checks;
use App\Facades\LibrenmsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegacyAlertLogController extends Controller
{
    public function index(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        Checks::postAuth();

        // Set up legacy environment
        $no_refresh = true;
        $init_modules = ['web', 'auth'];
        require base_path('/includes/init.php');

        // Set the page variable for legacy includes
        $vars['page'] = 'alert-log';
        $_GET['page'] = 'alert-log';
        $_REQUEST['page'] = 'alert-log';

        // Start output buffering to capture the legacy page output
        ob_start();
        
        try {
            // Include the legacy alert-log page
            require base_path('includes/html/pages/alert-log.inc.php');
            $content = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        // Return the legacy page wrapped in the legacy layout
        return response()->view('layouts.legacy_page', [
            'content' => $content,
            'refresh' => 0, // No refresh for alert log
        ]);
    }
}
