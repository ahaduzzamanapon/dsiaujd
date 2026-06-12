<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$empty = App\Models\Category::doesntHave('streams')->get();
echo "Total empty categories: " . $empty->count() . "\n";
foreach ($empty as $c) {
    echo "  ID:{$c->id} | {$c->name}\n";
}
