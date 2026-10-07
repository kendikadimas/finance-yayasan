import { Form, Head } from '@inertiajs/react';
import EwsSettingController from '@/actions/App/Http/Controllers/Keuangan/EwsSettingController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import { edit as ewsSettingEdit } from '@/routes/keuangan/ews-setting';
export default function EwsSettingEdit({ setting }) {
    return (
        <div className="flex flex-1 flex-col gap-6 p-4">
            <Head title="Konfigurasi EWS" />

            <Heading
                title="Konfigurasi Early Warning System"
                description="Atur ambang batas rasio serapan anggaran untuk klasifikasi status EWS tanpa mengubah kode program."
            />

            <Form
                {...EwsSettingController.update.form()}
                className="max-w-xl space-y-4 rounded-xl border p-4"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="batas_waspada"
                            >
                                Batas Waspada (rasio serapan &gt; nilai ini)
                            </label>
                            <Input
                                id="batas_waspada"
                                name="batas_waspada"
                                type="number"
                                step="0.01"
                                defaultValue={setting.batas_waspada}
                                required
                            />
                            <InputError message={errors.batas_waspada} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="batas_kritis"
                            >
                                Batas Kritis
                            </label>
                            <Input
                                id="batas_kritis"
                                name="batas_kritis"
                                type="number"
                                step="0.01"
                                defaultValue={setting.batas_kritis}
                                required
                            />
                            <InputError message={errors.batas_kritis} />
                        </div>
                        <div className="grid gap-1.5">
                            <label
                                className="text-sm font-medium"
                                htmlFor="batas_melebihi"
                            >
                                Batas Melebihi Anggaran
                            </label>
                            <Input
                                id="batas_melebihi"
                                name="batas_melebihi"
                                type="number"
                                step="0.01"
                                defaultValue={setting.batas_melebihi}
                                required
                            />
                            <InputError message={errors.batas_melebihi} />
                        </div>
                        <p className="text-sm text-muted-foreground">
                            Rasio Serapan = Persentase Serapan Aktual &divide;
                            Expected Pace (waktu berjalan &divide; total durasi
                            periode).
                        </p>
                        <Button disabled={processing}>Simpan</Button>
                    </>
                )}
            </Form>
        </div>
    );
}
EwsSettingEdit.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Konfigurasi EWS',
            href: ewsSettingEdit(),
        },
    ],
};
