<?php

namespace App\Workers\AVA\Services;

// Forwarding alias — GmailWatchService lives at the platform level.
// AVA job files and any legacy references can continue using this namespace.
class GmailWatchService extends \App\Platform\Services\Gmail\GmailWatchService {}
