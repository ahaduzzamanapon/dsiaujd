<?php

use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::create('/api/devices/ping', 'POST', [
    'uuid' => 'test-device-uuid-99999',
    'platform' => 'Android',
    'model' => 'Samsung S22 Ultra',
    'os_version' => '13',
    'app_version' => '1.2.0'
]);

$response = $kernel->handle($request);

echo "Response Status: " . $response->getStatusCode() . "\n";
echo "Response Content: " . $response->getContent() . "\n\n";

// Query DB to see if the device was inserted
$device = \App\Models\Device::where('uuid', 'test-device-uuid-99999')->first();
if ($device) {
    echo "SUCCESS: Device successfully registered in database!\n";
    echo "Model: " . $device->model . "\n";
    echo "Platform: " . $device->platform . "\n";
    echo "Last Ping: " . $device->last_ping_at . "\n";
    $device->delete();
    echo "Cleaned up test device record.\n";
} else {
    echo "FAILURE: Device was not found in the database!\n";
}
