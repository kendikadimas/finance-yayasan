import { cn } from '@/lib/utils';
const STYLES = {
    aman: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    waspada:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    kritis: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
    melebihi_anggaran:
        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
};
const LABELS = {
    aman: 'Aman',
    waspada: 'Waspada',
    kritis: 'Kritis',
    melebihi_anggaran: 'Melebihi Anggaran',
};
export function EwsBadge({ status }) {
    return (
        <span
            className={cn(
                'inline-flex w-fit shrink-0 items-center rounded-md border border-transparent px-2 py-0.5 text-xs font-medium whitespace-nowrap',
                STYLES[status] ?? 'bg-muted text-muted-foreground',
            )}
        >
            {LABELS[status] ?? status}
        </span>
    );
}
