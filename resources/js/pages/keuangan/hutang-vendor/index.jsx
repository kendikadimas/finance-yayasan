import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import HutangVendorController from '@/actions/App/Http/Controllers/Keuangan/HutangVendorController';
import { UnitFilter } from '@/components/unit-filter';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as hutangIndex } from '@/routes/keuangan/hutang-vendor';
export default function HutangVendorIndex({
    hutangs,
    units,
    vendors,
    kasBankAccounts,
    filterUnitId,
}) {
    const [editingId, setEditingId] = useState(null);
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Hutang Vendor" />

            <Heading
                title="Hutang Vendor"
                description="Catat dan pantau tagihan vendor yang belum dibayar."
            />

            <UnitFilter
                units={units}
                value={filterUnitId}
                action={hutangIndex().url}
            />

            <Form
                {...HutangVendorController.store.form()}
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
                                htmlFor="vendor_id"
                            >
                                Vendor
                            </label>
                            <NativeSelect
                                id="vendor_id"
                                name="vendor_id"
                                defaultValue=""
                                required
                            >
                                <option value="" disabled>
                                    Pilih vendor
                                </option>
                                {vendors.map((v) => (
                                    <option key={v.id} value={v.id}>
                                        {v.nama}
                                    </option>
                                ))}
                            </NativeSelect>
                            <InputError message={errors.vendor_id} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="nomor_invoice"
                            >
                                No. Invoice
                            </label>
                            <Input
                                id="nomor_invoice"
                                name="nomor_invoice"
                                placeholder="opsional"
                            />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="nominal"
                            >
                                Nominal
                            </label>
                            <Input
                                id="nominal"
                                name="nominal"
                                type="number"
                                min={0.01}
                                step="0.01"
                                required
                            />
                            <InputError message={errors.nominal} />
                        </div>
                        <div className="grid gap-1.5 md:col-span-2">
                            <label
                                className="text-sm font-medium"
                                htmlFor="deskripsi"
                            >
                                Deskripsi
                            </label>
                            <Input id="deskripsi" name="deskripsi" required />
                            <InputError message={errors.deskripsi} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="jatuh_tempo"
                            >
                                Jatuh Tempo
                            </label>
                            <Input
                                id="jatuh_tempo"
                                name="jatuh_tempo"
                                type="date"
                                required
                            />
                            <InputError message={errors.jatuh_tempo} />
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
                            <th className="p-3 font-medium">Vendor</th>
                            <th className="p-3 font-medium">Deskripsi</th>
                            <th className="p-3 font-medium">Nominal</th>
                            <th className="p-3 font-medium">Jatuh Tempo</th>
                            <th className="p-3 font-medium">Status</th>
                            <th className="p-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {hutangs.map((h) => {
                            const accountsForUnit = kasBankAccounts.filter(
                                (a) => a.unit_id === h.unit_id,
                            );
                            if (editingId === h.id) {
                                return (
                                    <tr
                                        key={h.id}
                                        className="border-t bg-muted/30"
                                    >
                                        <td colSpan={7} className="p-3">
                                            <Form
                                                {...HutangVendorController.update.form(
                                                    h.id,
                                                )}
                                                onSuccess={() =>
                                                    setEditingId(null)
                                                }
                                                className="grid grid-cols-1 gap-3 md:grid-cols-4"
                                            >
                                                {({ processing }) => (
                                                    <>
                                                        <NativeSelect
                                                            name="vendor_id"
                                                            defaultValue={
                                                                h.vendor.id
                                                            }
                                                            required
                                                        >
                                                            {vendors.map(
                                                                (v) => (
                                                                    <option
                                                                        key={
                                                                            v.id
                                                                        }
                                                                        value={
                                                                            v.id
                                                                        }
                                                                    >
                                                                        {v.nama}
                                                                    </option>
                                                                ),
                                                            )}
                                                        </NativeSelect>
                                                        <Input
                                                            name="nomor_invoice"
                                                            defaultValue={
                                                                h.nomor_invoice ??
                                                                ''
                                                            }
                                                            placeholder="opsional"
                                                        />
                                                        <Input
                                                            name="nominal"
                                                            type="number"
                                                            min={0.01}
                                                            step="0.01"
                                                            defaultValue={
                                                                h.nominal
                                                            }
                                                            required
                                                        />
                                                        <Input
                                                            name="jatuh_tempo"
                                                            type="date"
                                                            defaultValue={
                                                                h.jatuh_tempo
                                                            }
                                                            required
                                                        />
                                                        <Input
                                                            name="deskripsi"
                                                            defaultValue={
                                                                h.deskripsi
                                                            }
                                                            required
                                                            className="md:col-span-2"
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
                                <tr key={h.id} className="border-t align-top">
                                    <td className="p-3">{h.unit.nama}</td>
                                    <td className="p-3">{h.vendor.nama}</td>
                                    <td className="p-3">
                                        {h.deskripsi}
                                        {h.nomor_invoice && (
                                            <div className="text-xs text-muted-foreground">
                                                #{h.nomor_invoice}
                                            </div>
                                        )}
                                    </td>
                                    <td className="p-3">
                                        {formatRupiah(h.nominal)}
                                    </td>
                                    <td className="p-3 text-xs">
                                        {h.jatuh_tempo}
                                    </td>
                                    <td className="p-3 capitalize">
                                        {h.status.replace('_', ' ')}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex flex-col gap-2">
                                            {h.status !== 'lunas' && (
                                                <Form
                                                    {...HutangVendorController.lunas.form(
                                                        h.id,
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
                                                                name="tanggal_lunas"
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
                                                                Lunas
                                                            </Button>
                                                        </>
                                                    )}
                                                </Form>
                                            )}
                                            {h.status !== 'lunas' && (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setEditingId(h.id)
                                                    }
                                                >
                                                    Edit
                                                </Button>
                                            )}
                                            <Form
                                                {...HutangVendorController.destroy.form(
                                                    h.id,
                                                )}
                                                onSubmit={(e) => {
                                                    if (
                                                        !confirm(
                                                            'Hapus data hutang ini?',
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
                        {hutangs.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={7}
                                >
                                    Belum ada hutang vendor.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
HutangVendorIndex.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Hutang Vendor',
            href: hutangIndex(),
        },
    ],
};
