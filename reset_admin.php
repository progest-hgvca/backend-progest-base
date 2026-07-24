<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@admin.com')->first();
if ($user) {
    $user->password = Hash::make('admin');
    $user->save();
    echo "Password reset to 'admin'\n";
} else {
    echo "User not found\n";
}
