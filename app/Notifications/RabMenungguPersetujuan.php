<?php

namespace App\Notifications;

use App\Models\Anggaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class RabMenungguPersetujuan extends Notification
{
    use Queueable;

    public function __construct(public Anggaran $anggaran) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("RAB Menunggu Persetujuan - {$this->anggaran->unit->nama}")
            ->greeting('Persetujuan RAB Diperlukan')
            ->line("RAB \"{$this->anggaran->kategori}\" dari unit {$this->anggaran->unit->nama} menunggu persetujuan Anda.")
            ->line('Pagu diajukan: Rp '.number_format((float) $this->anggaran->pagu, 0, ',', '.'))
            ->line('Periode: '.Carbon::parse($this->anggaran->periode_mulai)->format('d M Y').' - '.Carbon::parse($this->anggaran->periode_selesai)->format('d M Y'))
            ->action('Tinjau RAB', url('/keuangan/anggaran'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'anggaran_id' => $this->anggaran->id,
            'unit_id' => $this->anggaran->unit_id,
            'unit' => $this->anggaran->unit->nama,
            'kategori' => $this->anggaran->kategori,
            'pagu' => (float) $this->anggaran->pagu,
            'message' => "RAB \"{$this->anggaran->kategori}\" unit {$this->anggaran->unit->nama} menunggu persetujuan Anda.",
        ];
    }
}
