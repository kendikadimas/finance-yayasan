import { Head } from '@inertiajs/react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    ReferenceLine,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { EwsBadge } from '@/components/ews-badge';
import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { UnitFilter } from '@/components/unit-filter';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import { dashboardEws } from '@/routes/keuangan/laporan';
const STATUS_COLOR = {
    aman: '#22c55e',
    waspada: '#eab308',
    kritis: '#f97316',
    melebihi_anggaran: '#ef4444',
};
export default function DashboardEws({
    anggarans,
    ringkasan,
    units,
    filterUnitId,
}) {
    const cards = [
        {
            key: 'aman',
            label: 'Aman',
        },
        {
            key: 'waspada',
            label: 'Waspada',
        },
        {
            key: 'kritis',
            label: 'Kritis',
        },
        {
            key: 'melebihi_anggaran',
            label: 'Melebihi Anggaran',
        },
    ];
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Dashboard EWS" />

            <Heading
                title="Dashboard Early Warning System"
                description="Ringkasan status serapan anggaran pengeluaran seluruh unit dan kategori."
            />

            <UnitFilter
                units={units}
                value={filterUnitId}
                action={dashboardEws().url}
            />

            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                {cards.map((c) => (
                    <Card key={c.key}>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {c.label}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-semibold">
                                {ringkasan[c.key] ?? 0}
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>

            {anggarans.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                            Rasio Serapan per Kategori
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer
                            width="100%"
                            height={Math.max(160, anggarans.length * 36)}
                        >
                            <BarChart
                                data={anggarans.map((a) => ({
                                    ...a,
                                    label: `${a.unit} - ${a.kategori}`,
                                }))}
                                layout="vertical"
                                margin={{
                                    left: 12,
                                    right: 24,
                                }}
                            >
                                <CartesianGrid
                                    horizontal={false}
                                    stroke="var(--border)"
                                />
                                <XAxis
                                    type="number"
                                    stroke="var(--muted-foreground)"
                                    fontSize={12}
                                />
                                <YAxis
                                    type="category"
                                    dataKey="label"
                                    width={180}
                                    stroke="var(--muted-foreground)"
                                    fontSize={12}
                                    tickLine={false}
                                />
                                <ReferenceLine
                                    x={1}
                                    stroke="var(--muted-foreground)"
                                    strokeDasharray="4 4"
                                />
                                <Tooltip
                                    formatter={(value) => [
                                        Number(value).toFixed(2),
                                        'Rasio Serapan',
                                    ]}
                                    contentStyle={{
                                        background: 'var(--popover)',
                                        border: '1px solid var(--border)',
                                        borderRadius: 8,
                                        color: 'var(--popover-foreground)',
                                    }}
                                />
                                <Bar
                                    dataKey="rasio_serapan"
                                    radius={[0, 4, 4, 0]}
                                    maxBarSize={18}
                                >
                                    {anggarans.map((a) => (
                                        <Cell
                                            key={a.id}
                                            fill={
                                                STATUS_COLOR[a.status_ews] ??
                                                '#999'
                                            }
                                        />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                        <div className="mt-3 flex flex-wrap gap-4 text-xs text-muted-foreground">
                            {Object.entries(STATUS_COLOR).map(
                                ([status, color]) => (
                                    <span
                                        key={status}
                                        className="flex items-center gap-1.5"
                                    >
                                        <span
                                            className="size-2.5 rounded-full"
                                            style={{
                                                backgroundColor: color,
                                            }}
                                        />
                                        {status === 'melebihi_anggaran'
                                            ? 'Melebihi Anggaran'
                                            : status.charAt(0).toUpperCase() +
                                              status.slice(1)}
                                    </span>
                                ),
                            )}
                        </div>
                    </CardContent>
                </Card>
            )}

            <div className="overflow-x-auto rounded-xl border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th className="p-3 font-medium">Unit</th>
                            <th className="p-3 font-medium">Kategori</th>
                            <th className="p-3 font-medium">Pagu</th>
                            <th className="p-3 font-medium">Realisasi</th>
                            <th className="p-3 font-medium">Rasio Serapan</th>
                            <th className="p-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {anggarans.map((a) => (
                            <tr key={a.id} className="border-t">
                                <td className="p-3">{a.unit}</td>
                                <td className="p-3">{a.kategori}</td>
                                <td className="p-3">{formatRupiah(a.pagu)}</td>
                                <td className="p-3">
                                    {formatRupiah(a.realisasi)}
                                </td>
                                <td className="p-3">
                                    {a.rasio_serapan.toFixed(2)}
                                </td>
                                <td className="p-3">
                                    <EwsBadge status={a.status_ews} />
                                </td>
                            </tr>
                        ))}
                        {anggarans.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={6}
                                >
                                    Belum ada RAB pengeluaran yang disetujui.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
DashboardEws.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Dashboard EWS',
            href: dashboardEws(),
        },
    ],
};
