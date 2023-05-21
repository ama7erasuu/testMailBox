<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Enums\StatusIdEnum;
use App\Models\Email;
use App\Models\Ticket;
use App\Repositories\EmailRepository;
use App\Repositories\TicketRepository;


class EmailService
{
    public function __construct(
        protected EmailRepository $emailRepository,
        protected TicketRepository $ticketRepository
    ) {
    }

    public function getEmail()
    {
        $emails = [];
        $tickets = [];
        $connect_imap = imap_open("{imap.yandex.ru:993/imap/ssl}INBOX", config('mail.user_name'), config('mail.password')) or die("Error:" . imap_last_error());
        $mails = imap_search($connect_imap, 'UNSEEN');
        if ($mails) {
            $emails = $this->parsMailData($mails, $connect_imap);
            $tickets = $this->parsDataToTicket($emails);
            foreach($emails as $mail){
                $this->emailRepository->create($mail);
            }
            foreach ($tickets as $ticketItem) {
                $ticket = $this->ticketRepository->getTicketById($ticketItem['id']);
                if (!$ticket) {
                    $this->ticketRepository->create($ticketItem);
                } else {
                    $this->ticketRepository->update($ticketItem, $ticketItem['id']);
                }
                echo "\n"."Заявка ".$ticketItem['id']." Переведена в статус - ".$ticketItem['status'];
            }
        } else {
            echo "Нет непрочитанных писем";
        }
        imap_close($connect_imap);
    }

    /**
     * @param [type] $subject
     * @return int
     */
    private function checkSubject($subject)
    {
        $status = null;
        $array_keys = [
            StatusIdEnum::New->value => StatusEnum::New->value,
            StatusIdEnum::InWork->value => StatusEnum::InWork->value,
            StatusIdEnum::Ready->value => StatusEnum::Ready->value,
            StatusIdEnum::Rejected->value => StatusEnum::Rejected->value,
        ];
        foreach ($array_keys as $k => $v) {
            $pos = mb_strpos(mb_strtolower($subject), $v);
            if ($pos) {
                $status = $k;
                break;
            }
        }
        if ($status) {
            return $status;
        } else {
            return 4;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function parsMailData(array $data, $connect_imap): array
    {
        foreach ($data as $num_mail) {
            $header = imap_headerinfo($connect_imap, $num_mail);
            $header = json_decode(json_encode($header), true);
            $subject = mb_decode_mimeheader($header['subject']);
            $body = imap_body($connect_imap, $num_mail);
            preg_match_all('|\d+|', $body, $matches);
            if (isset($matches[0][0])) {
                $id = $matches[0][0];
                $emails[] = [
                    'title' => $subject,
                    'body' => $id
                ];
            }
        }
        return $emails;
    }

    /**
     * @param array $data
     * @return array
     */
    private function parsDatatoTicket(array $data): array
    {
        $tickets=[];
        foreach($data as $v ){
            $tickets[]=[
                'id'=>$v['body'],
                'title'=>'Заявка '.$v['body'],
                'status'=>$this->checkSubject($v['title'])
            ];
        }
        return $tickets;
    }
}
