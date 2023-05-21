<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\Ticket;
use App\Services\EmailService;
use Illuminate\Console\Command;
use PhpImap\Mailbox;
use Webklex\IMAP\Facades\Client;

class ReadEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailbox:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected EmailService $emailService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     *
     */
    public function handle()
    {
        $lockFile = storage_path('app/lockfile.lock');
        if (file_exists($lockFile)) {
            $this->error('Команда уже запущена');
            return 1;
        }
        $handle = fopen($lockFile, 'w');
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            $this->error('Команда уже запущена.');
            return 1;
        }
        $this->emailService->getEmail();
        flock($handle, LOCK_UN);
        fclose($handle);
        unlink($lockFile);
    }
}
