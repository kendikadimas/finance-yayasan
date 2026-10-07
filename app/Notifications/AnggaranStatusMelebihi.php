<?php

namespace App\Notifications;

use App\Models\Anggaran;
use App\Services\EwsService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnggaranStatusMelebihi extends Notification
{
    use Queueable;

    public function __construct(
        public Anggaran $anggaran,
        public string $status,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = EwsService::label($this->status);
        $urgent = $this->status === EwsService::STATUS_MELEBIHI;

        return (new MailMessage)
            ->subject(($urgent ? '[Mendesak] ' : '')."Status EWS Naik ke {$label} - {$this->anggaran->unit->nama}")
            ->greeting('Peringatan Early Warning System')
            ->line("Status serapan anggaran \"{$this->anggaran->kategori}\" pada unit {$this->anggaran->unit->nama} naik ke level **{$label}**.")
            ->line('Pagu: Rp '.number_format((float) $this->anggaran->pagu, 0, ',', '.'))
            ->line('Realisasi saat ini: Rp '.number_format($this->anggaran->realisasi(), 0, ',', '.'))
            ->action('Lihat Dashboard EWS', url('/keuangan/laporan/dashboard-ews'))
            ->line('Mohon segera ditindaklanjuti.');
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
            'status' => $this->status,
            'status_label' => EwsService::label($this->status),
            'message' => "Status EWS anggaran \"{$this->anggaran->kategori}\" unit {$this->anggaran->unit->nama} naik ke level ".EwsService::label($this->status).'.',
        ];
    }
}
