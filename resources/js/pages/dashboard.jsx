import { Head, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
export default function Dashboard({ summary }) {
    const { notifications, auth } = usePage().props;
    return (
        <div className="flex h-full flex-1 flex-col gap-4 p-4">
            <Head title="Dashboard" />

            <h1 className="text-xl font-semibold">Halo, {auth.user.name}</h1>

            <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                {summary.scope === 'yayasan' ? (
                    <>
                        <SummaryCard
                            title="Pemasukan Bulan Ini"
                            value={formatRupiah(summary.totalPemasukanBulanIni)}
                            tone="green"
                        />
                        <SummaryCard
                            title="Pengeluaran Bulan Ini"
                            value={formatRupiah(
                                summary.totalPengeluaranBulanIni,
                            )}
                            tone="red"
                        />
                        <SummaryCard
                            title="Total Saldo Kas & Bank"
                            value={formatRupiah(summary.totalSaldoKasBank)}
                        />
                        <SummaryCard
                            title="Total Unit"
                            value={String(summary.totalUnit)}
                        />
                        <SummaryCard
                            title="RAB Menunggu Persetujuan"
                            value={String(summary.rabMenungguPersetujuan)}
                        />
                        <SummaryCard
                            title="Unit Status Kritis/Melebihi"
                            value={String(summary.unitKritisAtauMelebihi)}
                            tone={
                                summary.unitKritisAtauMelebihi > 0
                                    ? 'red'
                                    : undefined
                            }
                        />
                    </>
                ) : (
                    <>
                        <SummaryCard
                            title={`Pemasukan Bulan Ini - ${summary.unit ?? ''}`}
                            value={formatRupiah(summary.totalPemasukanBulanIni)}
                            tone="green"
                        />
                        <SummaryCard
                            title={`Pengeluaran Bulan Ini - ${summary.unit ?? ''}`}
                            value={formatRupiah(
                                summary.totalPengeluaranBulanIni,
                            )}
                            tone="red"
                        />
                        <SummaryCard
                            title="Saldo Kas & Bank Unit"
                            value={formatRupiah(summary.totalSaldoKasBank)}
                        />
                        <SummaryCard
                            title="Tagihan Siswa Belum Lunas"
                            value={String(summary.tagihanBelumLunas)}
                        />
                        <SummaryCard
                            title="RAB Draft"
                            value={String(summary.rabDraft)}
                        />
                        <SummaryCard
                            title="Kategori Status Kritis/Melebihi"
                            value={String(summary.unitKritisAtauMelebihi)}
                            tone={
                                summary.unitKritisAtauMelebihi > 0
                                    ? 'red'
                                    : undefined
                            }
                        />
                    </>
                )}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle className="text-sm font-medium text-muted-foreground">
                        Notifikasi Terbaru
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {notifications.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            Tidak ada notifikasi.
                        </p>
                    )}
                    <ul className="space-y-2">
                        {notifications.map((n) => (
                            <li key={n.id} className="text-sm">
                                {n.data.message}
                                <span className="ml-2 text-xs text-muted-foreground">
                                    {new Date(n.created_at).toLocaleString(
                                        'id-ID',
                                    )}
                                </span>
                            </li>
                        ))}
                    </ul>
                </CardContent>
            </Card>
        </div>
    );
}
function SummaryCard({ title, value, tone }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent
                className={`text-2xl font-semibold ${tone === 'green' ? 'text-green-600' : tone === 'red' ? 'text-red-600' : ''}`}
            >
                {value}
            </CardContent>
        </Card>
    );
}
Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
