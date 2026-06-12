<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$servers = App\Models\StreamServer::where('stream_type', 'iframe')->get();
echo "Total iframe servers: " . $servers->count() . "\n\n";
foreach ($servers as $s) {
    echo "ID:{$s->id} | NAME:{$s->name} | URL: {$s->url}\n";
}
