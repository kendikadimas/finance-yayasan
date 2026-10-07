import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import PenggajianController from '@/actions/App/Http/Controllers/Keuangan/PenggajianController';
import { UnitFilter } from '@/components/unit-filter';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as penggajianIndex } from '@/routes/keuangan/penggajian';
export default function PenggajianIndex({
    penggajians,
    units,
    kasBankAccounts,
    filterUnitId,
}) {
    const [editingId, setEditingId] = useState(null);
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Penggajian" />

            <Heading
                title="Penggajian"
                description="Hitung dan bayar gaji guru & karyawan per unit."
            />

            <UnitFilter
                units={units}
                value={filterUnitId}
                action={penggajianIndex().url}
            />

            <Form
                {...PenggajianController.store.form()}
                resetOnSuccess
                className="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-4"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="unit_id"
                            >
                                Unit
                            </label>
                            <NativeSelect
                                id="unit_id"
                                name="unit_id"
                                defaultValue={filterUnitId ?? ''}
                                required
                            >
                                <option value="" disabled>
                                    Pilih unit
                                </option>
                                {units.map((unit) => (
                                    <option key={unit.id} value={unit.id}>
                                        {unit.nama}
                                    </option>
                                ))}
                            </NativeSelect>
                            <InputError message={errors.unit_id} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="kode_pegawai"
                            >
                                Kode Pegawai
                            </label>
                            <Input
                                id="kode_pegawai"
                                name="kode_pegawai"
                                placeholder="opsional"
                            />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="nama_pegawai"
                            >
                                Nama Pegawai
                            </label>
                            <Input
                                id="nama_pegawai"
                                name="nama_pegawai"
                                required
                            />
                            <InputError message={errors.nama_pegawai} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="periode"
                            >
                                Periode
                            </label>
                            <Input
                                id="periode"
                                name="periode"
                                placeholder="2026-10"
                                required
                            />
                            <InputError message={errors.periode} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="gaji_pokok"
                            >
                                Gaji Pokok
                            </label>
                            <Input
                                id="gaji_pokok"
                                name="gaji_pokok"
                                type="number"
                                min={0}
                                step="0.01"
                                required
                            />
                            <InputError message={errors.gaji_pokok} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="tunjangan"
                            >
                                Tunjangan
                            </label>
                            <Input
                                id="tunjangan"
                                name="tunjangan"
                                type="number"
                                min={0}
                                step="0.01"
                                defaultValue={0}
                                required
                            />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="potongan"
                            >
                                Potongan
                            </label>
                            <Input
                                id="potongan"
                                name="potongan"
                                type="number"
                                min={0}
                                step="0.01"
                                defaultValue={0}
                                required
                            />
                        </div>
                        <div className="flex items-end">
                            <Button disabled={processing} className="w-full">
                                Tambah
                            </Button>
                        </div>
                    </>
                )}
            </Form>

            <div className="overflow-x-auto rounded-xl border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th className="p-3 font-medium">Unit</th>
                            <th className="p-3 font-medium">Pegawai</th>
                            <th className="p-3 font-medium">Periode</th>
                            <th className="p-3 font-medium">Total Gaji</th>
                            <th className="p-3 font-medium">Status</th>
                            <th className="p-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {penggajians.map((p) => {
                            const accountsForUnit = kasBankAccounts.filter(
                                (a) => a.unit_id === p.unit_id,
                            );
                            if (editingId === p.id) {
                                return (
                                    <tr
                                        key={p.id}
                                        className="border-t bg-muted/30"
                                    >
                                        <td colSpan={6} className="p-3">
                                            <Form
                                                {...PenggajianController.update.form(
                                                    p.id,
                                                )}
                                                onSuccess={() =>
                                                    setEditingId(null)
                                                }
                                                className="grid grid-cols-1 gap-3 md:grid-cols-4"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <Input
                                                            name="kode_pegawai"
                                                            defaultValue={
                                                                p.kode_pegawai ??
                                                                ''
                                                            }
                                                            placeholder="opsional"
                                                        />
                                                        <Input
                                                            name="nama_pegawai"
                                                            defaultValue={
                                                                p.nama_pegawai
                                                            }
                                                            required
                                                        />
                                                        <Input
                                                            name="periode"
                                                            defaultValue={
                                                                p.periode
                                                            }
                                                            required
                                                        />
                                                        <Input
                                                            name="gaji_pokok"
                                                            type="number"
                                                            min={0}
                                                            step="0.01"
                                                            defaultValue={
                                                                p.gaji_pokok
                                                            }
                                                            required
                                                        />
                                                        <Input
                                                            name="tunjangan"
                                                            type="number"
                                                            min={0}
                                                            step="0.01"
                                                            defaultValue={
                                                                p.tunjangan
                                                            }
                                                            required
                                                        />
                                                        <Input
                                                            name="potongan"
                                                            type="number"
                                                            min={0}
                                                            step="0.01"
                                                            defaultValue={
                                                                p.potongan
                                                            }
                                                            required
                                                        />
                                                        <div className="flex gap-2">
                                                            <Button
                                                                size="sm"
                                                                disabled={
                                                                    processing
                                                                }
                                                            >
                                                                Simpan
                                                            </Button>
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() =>
                                                                    setEditingId(
                                                                        null,
                                                                    )
                                                                }
                                                            >
                                                                Batal
                                                            </Button>
                                                        </div>
                                                    </>
                                                )}
                                            </Form>
                                        </td>
                                    </tr>
                                );
                            }
                            return (
                                <tr key={p.id} className="border-t align-top">
                                    <td className="p-3">{p.unit.nama}</td>
                                    <td className="p-3">
                                        {p.nama_pegawai}
                                        {p.kode_pegawai && (
                                            <div className="text-xs text-muted-foreground">
                                                {p.kode_pegawai}
                                            </div>
                                        )}
                                    </td>
                                    <td className="p-3">{p.periode}</td>
                                    <td className="p-3">
                                        {formatRupiah(p.total_gaji)}
                                    </td>
                                    <td className="p-3 capitalize">
                                        {p.status}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex flex-col gap-2">
                                            {p.status === 'draft' && (
                                                <Form
                                                    {...PenggajianController.bayar.form(
                                                        p.id,
                                                    )}
                                                    className="flex flex-wrap items-end gap-2"
                                                >
                                                    {({ processing }) => (
                                                        <>
                                                            <NativeSelect
                                                                name="kas_bank_account_id"
                                                                className="w-32"
                                                                required
                                                                defaultValue=""
                                                            >
                                                                <option
                                                                    value=""
                                                                    disabled
                                                                >
                                                                    Akun
                                                                </option>
                                                                {accountsForUnit.map(
                                                                    (a) => (
                                                                        <option
                                                                            key={
                                                                                a.id
                                                                            }
                                                                            value={
                                                                                a.id
                                                                            }
                                                                        >
                                                                            {
                                                                                a.nama
                                                                            }
                                                                        </option>
                                                                    ),
                                                                )}
                                                            </NativeSelect>
                                                            <Input
                                                                name="tanggal_bayar"
                                                                type="date"
                                                                defaultValue={new Date()
                                                                    .toISOString()
                                                                    .slice(
                                                                        0,
                                                                        10,
                                                                    )}
                                                                className="w-36"
                                                                required
                                                            />
                                                            <Button
                                                                size="sm"
                                                                disabled={
                                                                    processing
                                                                }
                                                            >
                                                                Bayar
                                                            </Button>
                                                        </>
                                                    )}
                                                </Form>
                                            )}
                                            {p.status === 'draft' && (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setEditingId(p.id)
                                                    }
                                                >
                                                    Edit
                                                </Button>
                                            )}
                                            <Form
                                                {...PenggajianController.destroy.form(
                                                    p.id,
                                                )}
                                                onSubmit={(e) => {
                                                    if (
                                                        !confirm(
                                                            `Hapus data gaji ${p.nama_pegawai}?`,
                                                        )
                                                    )
                                                        e.preventDefault();
                                                }}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                        size="sm"
                                                        disabled={processing}
                                                    >
                                                        Hapus
                                                    </Button>
                                                )}
                                            </Form>
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                        {penggajians.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={6}
                                >
                                    Belum ada data penggajian.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
PenggajianIndex.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Penggajian',
            href: penggajianIndex(),
        },
    ],
};
