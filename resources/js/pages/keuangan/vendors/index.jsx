import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import VendorController from '@/actions/App/Http/Controllers/Keuangan/VendorController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import { index as vendorsIndex } from '@/routes/keuangan/vendors';
export default function VendorsIndex({ vendors }) {
    const [editingId, setEditingId] = useState(null);
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Vendor" />

            <Heading
                title="Vendor / Rekanan"
                description="Kelola daftar vendor untuk pencatatan hutang & pengeluaran."
            />

            <Form
                {...VendorController.store.form()}
                resetOnSuccess
                className="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-3"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="nama"
                            >
                                Nama Vendor
                            </label>
                            <Input
                                id="nama"
                                name="nama"
                                placeholder="CV Sumber Makmur"
                                required
                            />
                            <InputError message={errors.nama} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="kontak"
                            >
                                Kontak
                            </label>
                            <Input
                                id="kontak"
                                name="kontak"
                                placeholder="08xxxxxxxxxx"
                            />
                            <InputError message={errors.kontak} />
                        </div>
                        <div className="flex items-end">
                            <Button disabled={processing} className="w-full">
                                Tambah Vendor
                            </Button>
                        </div>
                    </>
                )}
            </Form>

            <div className="overflow-x-auto rounded-xl border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th className="p-3 font-medium">Nama</th>
                            <th className="p-3 font-medium">Kontak</th>
                            <th className="p-3 font-medium">Status Hutang</th>
                            <th className="p-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {vendors.map((vendor) =>
                            editingId === vendor.id ? (
                                <tr
                                    key={vendor.id}
                                    className="border-t bg-muted/30"
                                >
                                    <td colSpan={4} className="p-3">
                                        <Form
                                            {...VendorController.update.form(
                                                vendor.id,
                                            )}
                                            onSuccess={() => setEditingId(null)}
                                            className="grid grid-cols-1 gap-3 md:grid-cols-4"
                                        >
                                            {({ processing }) => (
                                                <>
                                                    <Input
                                                        name="nama"
                                                        defaultValue={
                                                            vendor.nama
                                                        }
                                                        required
                                                    />
                                                    <Input
                                                        name="kontak"
                                                        defaultValue={
                                                            vendor.kontak ?? ''
                                                        }
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
                                <tr key={vendor.id} className="border-t">
                                    <td className="p-3">{vendor.nama}</td>
                                    <td className="p-3">
                                        {vendor.kontak ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        {vendor.status_hutang === 'lunas'
                                            ? 'Lunas'
                                            : 'Belum Lunas'}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex items-center gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    setEditingId(vendor.id)
                                                }
                                            >
                                                Edit
                                            </Button>
                                            <Form
                                                {...VendorController.destroy.form(
                                                    vendor.id,
                                                )}
                                                onSubmit={(e) => {
                                                    if (
                                                        !confirm(
                                                            `Hapus vendor ${vendor.nama}?`,
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
                        {vendors.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={4}
                                >
                                    Belum ada vendor.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
VendorsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Vendor',
            href: vendorsIndex(),
        },
    ],
};
