<?php

namespace App\Workers\AVA\Services;

// Forwarding alias — GmailService lives at the platform level.
// AVA job files and any legacy references can continue using this namespace.
class GmailService extends \App\Platform\Services\Gmail\GmailService {}
