import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import KasBankAccountController from '@/actions/App/Http/Controllers/Keuangan/KasBankAccountController';
import { UnitFilter } from '@/components/unit-filter';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { formatRupiah } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as kasBankIndex } from '@/routes/keuangan/kas-bank';
export default function KasBankIndex({ accounts, units, filterUnitId }) {
    const [editingId, setEditingId] = useState(null);
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Kas & Bank" />

            <Heading
                title="Kas & Bank"
                description="Kelola saldo kas dan rekening bank per unit."
            />

            <UnitFilter
                units={units}
                value={filterUnitId}
                action={kasBankIndex().url}
            />

            <Form
                {...KasBankAccountController.store.form()}
                resetOnSuccess
                className="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-5"
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
                                htmlFor="nama"
                            >
                                Nama Akun
                            </label>
                            <Input
                                id="nama"
                                name="nama"
                                placeholder="Kas Tunai / Bank BCA"
                                required
                            />
                            <InputError message={errors.nama} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="jenis"
                            >
                                Jenis
                            </label>
                            <NativeSelect
                                id="jenis"
                                name="jenis"
                                defaultValue="kas"
                                required
                            >
                                <option value="kas">Kas</option>
                                <option value="bank">Bank</option>
                            </NativeSelect>
                            <InputError message={errors.jenis} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="nomor_rekening"
                            >
                                No. Rekening
                            </label>
                            <Input
                                id="nomor_rekening"
                                name="nomor_rekening"
                                placeholder="opsional"
                            />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="saldo"
                            >
                                Saldo Awal
                            </label>
                            <Input
                                id="saldo"
                                name="saldo"
                                type="number"
                                min={0}
                                step="0.01"
                                defaultValue={0}
                                required
                            />
                            <InputError message={errors.saldo} />
                        </div>
                        <div className="md:col-span-5">
                            <Button disabled={processing}>Tambah Akun</Button>
                        </div>
                    </>
                )}
            </Form>

            <div className="overflow-x-auto rounded-xl border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th className="p-3 font-medium">Unit</th>
                            <th className="p-3 font-medium">Nama Akun</th>
                            <th className="p-3 font-medium">Jenis</th>
                            <th className="p-3 font-medium">No. Rekening</th>
                            <th className="p-3 font-medium">Saldo</th>
                            <th className="p-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {accounts.map((account) =>
                            editingId === account.id ? (
                                <tr
                                    key={account.id}
                                    className="border-t bg-muted/30"
                                >
                                    <td colSpan={6} className="p-3">
                                        <Form
                                            {...KasBankAccountController.update.form(
                                                account.id,
                                            )}
                                            onSuccess={() => setEditingId(null)}
                                            className="grid grid-cols-1 gap-3 md:grid-cols-4"
                                        >
                                            {({ processing }) => (
                                                <>
                                                    <Input
                                                        name="nama"
                                                        defaultValue={
                                                            account.nama
                                                        }
                                                        required
                                                    />
                                                    <NativeSelect
                                                        name="jenis"
                                                        defaultValue={
                                                            account.jenis
                                                        }
                                                        required
                                                    >
                                                        <option value="kas">
                                                            Kas
                                                        </option>
                                                        <option value="bank">
                                                            Bank
                                                        </option>
                                                    </NativeSelect>
                                                    <Input
                                                        name="nomor_rekening"
                                                        defaultValue={
                                                            account.nomor_rekening ??
                                                            ''
                                                        }
                                                        placeholder="opsional"
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
                            ) : (
                                <tr key={account.id} className="border-t">
                                    <td className="p-3">{account.unit.nama}</td>
                                    <td className="p-3">{account.nama}</td>
                                    <td className="p-3">
                                        {account.jenis === 'kas'
                                            ? 'Kas'
                                            : 'Bank'}
                                    </td>
                                    <td className="p-3">
                                        {account.nomor_rekening ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        {formatRupiah(account.saldo)}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex items-center gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    setEditingId(account.id)
                                                }
                                            >
                                                Edit
                                            </Button>
                                            <Form
                                                {...KasBankAccountController.destroy.form(
                                                    account.id,
                                                )}
                                                onSubmit={(e) => {
                                                    if (
                                                        !confirm(
                                                            `Hapus akun ${account.nama}?`,
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
                            ),
                        )}
                        {accounts.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={6}
                                >
                                    Belum ada akun kas/bank.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
KasBankIndex.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Kas & Bank',
            href: kasBankIndex(),
        },
    ],
};
