<?php
/**
 * Full Laravel Boot Diagnostic
 * Menampilkan error SEBENARNYA yang menyebabkan Error 500
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Suppress include warnings dari ClassLoader (sudah diketahui)
set_error_handler(function($severity, $message, $file, $line) {
    // Skip include warnings dari ClassLoader.php
    if (strpos($file, 'ClassLoader.php') !== false && strpos($message, 'include') !== false) {
        return true;
    }
    // Tampilkan error lainnya
    echo "<p style='color:#fbbf24;font-size:12px;'>⚠️ Warning: $message in $file:$line</p>";
    return true;
});

echo "<html><head><title>Diagnosa</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;} pre{background:#1e293b;padding:12px;border-radius:8px;font-size:11px;overflow-x:auto;}
</style></head><body>";

echo "<h2>🔍 Full Laravel Boot Diagnostic</h2>";

try {
    echo "<p>1️⃣ Loading autoloader...</p>";
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    echo "<p class='ok'>✅ Autoloader OK</p>";
    
    echo "<p>2️⃣ Creating Laravel Application...</p>";
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    echo "<p class='ok'>✅ Application created</p>";
    
    echo "<p>3️⃣ Building Kernel...</p>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<p class='ok'>✅ Kernel OK</p>";
    
    echo "<p>4️⃣ Handling Request...</p>";
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "<p class='ok'>✅ Response OK (Status: " . $response->getStatusCode() . ")</p>";
    
    echo "<h2 class='ok'>🎉 LARAVEL BOOTS SUCCESSFULLY!</h2>";
    echo "<p>Status Code: " . $response->getStatusCode() . "</p>";
    
    if ($response->getStatusCode() === 500) {
        echo "<p class='err'>Tapi response masih 500. Kemungkinan error di view/controller.</p>";
        // Coba tampilkan content
        $content = $response->getContent();
        if (strlen($content) > 0) {
            echo "<h3>Response Content:</h3>";
            echo "<pre>" . htmlspecialchars(substr($content, 0, 3000)) . "</pre>";
        }
    }
    
} catch (Throwable $e) {
    echo "<p class='err'>❌ FATAL ERROR:</p>";
    echo "<pre style='color:#f87171;'>" . get_class($e) . ": " . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
