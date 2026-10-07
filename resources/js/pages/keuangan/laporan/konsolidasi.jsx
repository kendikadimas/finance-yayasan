import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import { konsolidasi } from '@/routes/keuangan/laporan';
export default function LaporanKonsolidasi({
    perUnit,
    totalPemasukan,
    totalPengeluaran,
    totalSaldoKasBank,
    periode,
}) {
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Laporan Konsolidasi" />

            <Heading
                title="Laporan Konsolidasi Yayasan"
                description={`Gabungan kondisi keuangan seluruh unit periode ${periode.dari} s.d. ${periode.sampai}.`}
            />

            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                            Total Pemasukan Yayasan
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-2xl font-semibold text-green-600">
                        {formatRupiah(totalPemasukan)}
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                            Total Pengeluaran Yayasan
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-2xl font-semibold text-red-600">
                        {formatRupiah(totalPengeluaran)}
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                            Total Saldo Kas & Bank
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-2xl font-semibold">
                        {formatRupiah(totalSaldoKasBank)}
                    </CardContent>
                </Card>
            </div>

            <div className="overflow-x-auto rounded-xl border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th className="p-3 font-medium">Unit</th>
                            <th className="p-3 font-medium">Pemasukan</th>
                            <th className="p-3 font-medium">Pengeluaran</th>
                            <th className="p-3 font-medium">Saldo Bersih</th>
                            <th className="p-3 font-medium">
                                Saldo Kas & Bank
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {perUnit.map((row) => (
                            <tr key={row.unit_id} className="border-t">
                                <td className="p-3">{row.unit}</td>
                                <td className="p-3 text-green-600">
                                    {formatRupiah(row.pemasukan)}
                                </td>
                                <td className="p-3 text-red-600">
                                    {formatRupiah(row.pengeluaran)}
                                </td>
                                <td className="p-3">
                                    {formatRupiah(row.saldo_bersih)}
                                </td>
                                <td className="p-3">
                                    {formatRupiah(row.saldo_kas_bank)}
                                </td>
                            </tr>
                        ))}
                        {perUnit.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={5}
                                >
                                    Belum ada data unit.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
LaporanKonsolidasi.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Laporan Konsolidasi',
            href: konsolidasi(),
        },
    ],
};
