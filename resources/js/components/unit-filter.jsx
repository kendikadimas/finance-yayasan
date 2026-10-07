import { useRef } from 'react';
import { NativeSelect } from '@/components/ui/native-select';
/**
 * Filter unit sederhana berbasis GET form native: memilih unit me-reload halaman dengan
 * query `?unit_id=`. Server mengabaikan parameter ini untuk Bendahara Unit (selalu dikunci
 * ke unitnya sendiri).
 */
export function UnitFilter({ units, value, action }) {
    const formRef = useRef(null);
    return (
        <form
            ref={formRef}
            method="get"
            action={action}
            className="flex items-center gap-2"
        >
            <label className="text-sm font-medium" htmlFor="unit_id">
                Unit
            </label>
            <NativeSelect
                id="unit_id"
                name="unit_id"
                defaultValue={value ?? ''}
                className="w-56"
                onChange={() => formRef.current?.submit()}
            >
                <option value="">Semua Unit</option>
                {units.map((unit) => (
                    <option key={unit.id} value={unit.id}>
                        {unit.nama}
                    </option>
                ))}
            </NativeSelect>
        </form>
    );
}
