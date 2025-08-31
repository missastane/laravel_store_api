<?php

namespace App\Jobs;

use App\Http\Services\Message\Email\EmailService;
use App\Http\Services\Message\MessageService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class SendResetPasswordUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $token;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = url("/api/password/reset?token={$this->token}&email={$this->user->email}");
        $emailService = new EmailService();
        $details = [
            'title' => 'لینک تغییر رمز عبور حساب کاربری شما',
            'body' => $url
        ];
        $emailService->setDetails($details);
        $emailService->setFrom('noreply@example.com', 'amazon');
        $emailService->setSubject('لینک تغییر رمز عبور');
        $emailService->setTo($this->user->email);
        $messageService = new MessageService($emailService);
        $messageService->send();
    }

    public function failed(\Exception $exception)
{
    Log::error("جاب ارسال لینک تغییر رمز عبور برای ایمیل".$this->user->email." با خطا مواجه شد : ". $exception->getMessage());
}
}
