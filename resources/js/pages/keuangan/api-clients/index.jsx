import { Form, Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ApiClientController from '@/actions/App/Http/Controllers/Keuangan/ApiClientController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import { index as apiClientsIndex } from '@/routes/keuangan/api-clients';

function useFlashApiToken() {
    const [token, setToken] = useState(null);

    useEffect(() => {
        return router.on('flash', (event) => {
            const value = event.detail?.flash?.apiToken;
            if (value) {
                setToken(value);
            }
        });
    }, []);

    return [token, setToken];
}

function AbilityCheckboxes({ abilityOptions, defaultSelected = [], errors }) {
    return (
        <div className="grid gap-1.5">
            <span className="text-sm font-medium">Akses Data (Ability)</span>
            <div className="grid gap-1.5 sm:grid-cols-2">
                {abilityOptions.map((ability) => (
                    <label
                        key={ability.value}
                        className="flex items-center gap-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            name="abilities[]"
                            value={ability.value}
                            defaultChecked={defaultSelected.includes(
                                ability.value,
                            )}
                            className="size-4 rounded border-input"
                        />
                        {ability.label}
                    </label>
                ))}
            </div>
            <InputError message={errors?.abilities} />
        </div>
    );
}

function AbilityBadges({ abilities, abilityOptions }) {
    if (!abilities || abilities.length === 0) {
        return <span className="text-muted-foreground">-</span>;
    }

    return (
        <div className="flex flex-wrap gap-1">
            {abilities.map((value) => {
                const option = abilityOptions.find((o) => o.value === value);
                return (
                    <span
                        key={value}
                        className="rounded-full bg-muted px-2 py-0.5 text-xs"
                    >
                        {option?.label ?? value}
                    </span>
                );
            })}
        </div>
    );
}

export default function ApiClientsIndex({ clients, abilityOptions }) {
    const [token, setToken] = useFlashApiToken();
    const [editingId, setEditingId] = useState(null);

    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Klien API" />

            <Heading
                title="Klien API"
                description="Kelola akses sistem eksternal (paket lain dalam Sistem Terintegrasi Yayasan) untuk mengambil data dari sistem keuangan ini via API."
            />

            <Dialog
                open={!!token}
                onOpenChange={(open) => !open && setToken(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Token API</DialogTitle>
                        <DialogDescription>
                            Salin token ini sekarang. Token tidak akan
                            ditampilkan lagi setelah dialog ini ditutup.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="flex items-center gap-2">
                        <Input
                            readOnly
                            value={token ?? ''}
                            className="font-mono text-xs"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() =>
                                navigator.clipboard.writeText(token ?? '')
                            }
                        >
                            Salin
                        </Button>
                    </div>
                    <DialogFooter>
                        <Button type="button" onClick={() => setToken(null)}>
                            Selesai
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Form
                {...ApiClientController.store.form()}
                resetOnSuccess
                className="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-3"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-1.5 md:col-span-1">
                            <label
                                className="text-sm font-medium"
                                htmlFor="nama"
                            >
                                Nama Klien
                            </label>
                            <Input id="nama" name="nama" required />
                            <InputError message={errors.nama} />
                        </div>
                        <div className="grid gap-1.5 md:col-span-1">
                            <label
                                className="text-sm font-medium"
                                htmlFor="deskripsi"
                            >
                                Deskripsi
                            </label>
                            <Input id="deskripsi" name="deskripsi" />
                            <InputError message={errors.deskripsi} />
                        </div>
                        <div className="flex items-end md:col-span-1">
                            <Button disabled={processing}>
                                Buat Klien & Terbitkan Token
                            </Button>
                        </div>
                        <div className="md:col-span-3">
                            <AbilityCheckboxes
                                abilityOptions={abilityOptions}
                                errors={errors}
                            />
                        </div>
                    </>
                )}
            </Form>

            <div className="overflow-x-auto rounded-xl border">
                <table className="w-full text-sm">
                    <thead className="bg-muted/50 text-left">
                        <tr>
                            <th className="p-3 font-medium">Nama</th>
                            <th className="p-3 font-medium">Deskripsi</th>
                            <th className="p-3 font-medium">Ability</th>
                            <th className="p-3 font-medium">Status</th>
                            <th className="p-3 font-medium">
                                Terakhir Dipakai
                            </th>
                            <th className="p-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {clients.map((client) =>
                            editingId === client.id ? (
                                <tr
                                    key={client.id}
                                    className="border-t bg-muted/30"
                                >
                                    <td colSpan={6} className="p-3">
                                        <Form
                                            {...ApiClientController.update.form(
                                                client.id,
                                            )}
                                            onSuccess={() => setEditingId(null)}
                                            className="grid grid-cols-1 gap-3 md:grid-cols-3"
                                        >
                                            {({ processing, errors }) => (
                                                <>
                                                    <Input
                                                        name="nama"
                                                        defaultValue={
                                                            client.nama
                                                        }
                                                        required
                                                    />
                                                    <Input
                                                        name="deskripsi"
                                                        defaultValue={
                                                            client.deskripsi ??
                                                            ''
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
                                                    <div className="md:col-span-3">
                                                        <AbilityCheckboxes
                                                            abilityOptions={
                                                                abilityOptions
                                                            }
                                                            defaultSelected={
                                                                client.abilities ??
                                                                []
                                                            }
                                                            errors={errors}
                                                        />
                                                    </div>
                                                    <p className="text-xs text-muted-foreground md:col-span-3">
                                                        Setelah menyimpan
                                                        perubahan ability, klik
                                                        &quot;Terbitkan
                                                        Ulang&quot; agar token
                                                        yang sudah ada ikut
                                                        memakai ability baru.
                                                    </p>
                                                </>
                                            )}
                                        </Form>
                                    </td>
                                </tr>
                            ) : (
                                <tr key={client.id} className="border-t">
                                    <td className="p-3">{client.nama}</td>
                                    <td className="p-3 text-muted-foreground">
                                        {client.deskripsi ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        <AbilityBadges
                                            abilities={client.abilities}
                                            abilityOptions={abilityOptions}
                                        />
                                    </td>
                                    <td className="p-3">
                                        {client.is_aktif ? (
                                            <span className="text-green-600">
                                                Aktif
                                            </span>
                                        ) : (
                                            <span className="text-muted-foreground">
                                                Nonaktif
                                            </span>
                                        )}
                                    </td>
                                    <td className="p-3">
                                        {client.last_used_at ?? '-'}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    setEditingId(client.id)
                                                }
                                            >
                                                Edit
                                            </Button>
                                            <Form
                                                {...ApiClientController.regenerateToken.form(
                                                    client.id,
                                                )}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        size="sm"
                                                        variant="outline"
                                                        disabled={processing}
                                                        onClick={(e) => {
                                                            if (
                                                                !confirm(
                                                                    `Terbitkan ulang token untuk ${client.nama}? Token lama akan langsung tidak berlaku.`,
                                                                )
                                                            )
                                                                e.preventDefault();
                                                        }}
                                                    >
                                                        Terbitkan Ulang
                                                    </Button>
                                                )}
                                            </Form>
                                            <Form
                                                {...ApiClientController.toggleActive.form(
                                                    client.id,
                                                )}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        size="sm"
                                                        variant="outline"
                                                        disabled={processing}
                                                    >
                                                        {client.is_aktif
                                                            ? 'Nonaktifkan'
                                                            : 'Aktifkan'}
                                                    </Button>
                                                )}
                                            </Form>
                                            <Form
                                                {...ApiClientController.destroy.form(
                                                    client.id,
                                                )}
                                                onSubmit={(e) => {
                                                    if (
                                                        !confirm(
                                                            `Hapus klien ${client.nama}?`,
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
                        {clients.length === 0 && (
                            <tr>
                                <td
                                    className="p-3 text-muted-foreground"
                                    colSpan={6}
                                >
                                    Belum ada klien API.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
ApiClientsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Klien API',
            href: apiClientsIndex(),
        },
    ],
};
