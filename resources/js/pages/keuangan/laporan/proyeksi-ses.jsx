import { Form, Head } from '@inertiajs/react';
import { useRef } from 'react';
import {
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import LaporanController from '@/actions/App/Http/Controllers/Keuangan/LaporanController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { NativeSelect } from '@/components/ui/native-select';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import { proyeksiSes } from '@/routes/keuangan/laporan';
export default function ProyeksiSes({
    unit,
    kategoriTersedia,
    kategori,
    deretHistoris,
    hasil,
    cukupData,
    minBulan,
}) {
    const formRef = useRef(null);
    const bulan = Object.entries(deretHistoris);
    const chartData = bulan.map(([periode, nilai]) => ({
        periode,
        aktual: nilai,
        proyeksi: undefined,
    }));
    if (hasil && chartData.length > 0) {
        chartData[chartData.length - 1].proyeksi =
            chartData[chartData.length - 1].aktual;
        chartData.push({
            periode: 'Proyeksi',
            aktual: undefined,
            proyeksi: hasil.proyeksi,
        });
    }
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title={`Proyeksi SES - ${unit.nama}`} />

            <Heading
                title={`Proyeksi Anggaran (SES) - ${unit.nama}`}
                description="Proyeksi realisasi pengeluaran periode berikutnya menggunakan Single Exponential Smoothing, dioptimalkan dengan MAPE."
            />

            <form
                ref={formRef}
                method="get"
                action={proyeksiSes(unit.id).url}
                className="flex items-center gap-2"
            >
                <label className="text-sm font-medium" htmlFor="kategori">
                    Kategori Pengeluaran
                </label>
                <NativeSelect
                    id="kategori"
                    name="kategori"
                    defaultValue={kategori ?? ''}
                    className="w-64"
                    onChange={() => formRef.current?.submit()}
                >
                    {kategoriTersedia.map((k) => (
                        <option key={k} value={k}>
                            {k}
                        </option>
                    ))}
                </NativeSelect>
            </form>

            {!cukupData && (
                <p className="rounded-xl border border-dashed p-4 text-sm text-muted-foreground">
                    Data historis belum cukup. Minimal {minBulan} bulan
                    transaksi pengeluaran dibutuhkan untuk kategori ini
                    (tersedia {bulan.length}
                    bulan).
                </p>
            )}

            {cukupData && hasil && (
                <>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Alpha Terbaik
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-2xl font-semibold">
                                {hasil.alpha}
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    MAPE
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-2xl font-semibold">
                                {hasil.mape.toFixed(2)}%
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    Proyeksi Periode Berikutnya
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-2xl font-semibold">
                                {formatRupiah(hasil.proyeksi)}
                            </CardContent>
                        </Card>
                    </div>

                    <Form
                        {...LaporanController.simpanProyeksiSes.form(unit.id)}
                        className="w-fit"
                    >
                        {({ processing }) => (
                            <>
                                <input
                                    type="hidden"
                                    name="kategori"
                                    value={kategori ?? ''}
                                />
                                <Button disabled={processing}>
                                    Simpan Proyeksi Ini
                                </Button>
                            </>
                        )}
                    </Form>
                </>
            )}

            {chartData.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                            Tren Realisasi & Proyeksi
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer width="100%" height={280}>
                            <LineChart
                                data={chartData}
                                margin={{
                                    left: 8,
                                    right: 16,
                                    top: 8,
                                }}
                            >
                                <CartesianGrid
                                    stroke="var(--border)"
                                    vertical={false}
                                />
                                <XAxis
                                    dataKey="periode"
                                    stroke="var(--muted-foreground)"
                                    fontSize={12}
                                />
                                <YAxis
                                    stroke="var(--muted-foreground)"
                                    fontSize={12}
                                    tickFormatter={(v) => formatRupiah(v)}
                                    width={90}
                                />
                                <Tooltip
                                    formatter={(value) =>
                                        formatRupiah(Number(value))
                                    }
                                    contentStyle={{
                                        background: 'var(--popover)',
                                        border: '1px solid var(--border)',
                                        borderRadius: 8,
                                        color: 'var(--popover-foreground)',
                                    }}
                                />
                                <Legend
                                    wrapperStyle={{
                                        fontSize: 12,
                                    }}
                                />
                                <Line
                                    type="monotone"
                                    dataKey="aktual"
                                    name="Realisasi Aktual"
                                    stroke="#2a78d6"
                                    strokeWidth={2}
                                    dot={{
                                        r: 3,
                                    }}
                                    connectNulls={false}
                                />
                                <Line
                                    type="monotone"
                                    dataKey="proyeksi"
                                    name="Proyeksi"
                                    stroke="#eb6834"
                                    strokeWidth={2}
                                    strokeDasharray="5 5"
                                    dot={{
                                        r: 3,
                                    }}
                                    connectNulls
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    </CardContent>
                </Card>
            )}

            <div>
                <Heading
                    variant="small"
                    title="Deret Historis Realisasi Bulanan"
                />
                <div className="mt-2 overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="p-3 font-medium">Periode</th>
                                <th className="p-3 font-medium">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {bulan.map(([periode, nilai]) => (
                                <tr key={periode} className="border-t">
                                    <td className="p-3">{periode}</td>
                                    <td className="p-3">
                                        {formatRupiah(nilai)}
                                    </td>
                                </tr>
                            ))}
                            {bulan.length === 0 && (
                                <tr>
                                    <td
                                        className="p-3 text-muted-foreground"
                                        colSpan={2}
                                    >
                                        Belum ada data historis untuk kategori
                                        ini.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
ProyeksiSes.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Proyeksi SES',
            href: '#',
        },
    ],
};
