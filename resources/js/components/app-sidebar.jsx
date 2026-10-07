import { Link, usePage } from '@inertiajs/react';
import {
    Banknote,
    Building2,
    CreditCard,
    FileWarning,
    HandCoins,
    KeyRound,
    LayoutGrid,
    Receipt,
    ReceiptText,
    Settings2,
    SlidersHorizontal,
    Users,
    Wallet,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as anggaranIndex } from '@/routes/keuangan/anggaran';
import { index as apiClientsIndex } from '@/routes/keuangan/api-clients';
import { edit as ewsSettingEdit } from '@/routes/keuangan/ews-setting';
import { index as hutangVendorIndex } from '@/routes/keuangan/hutang-vendor';
import { index as kasBankIndex } from '@/routes/keuangan/kas-bank';
import { dashboardEws, konsolidasi } from '@/routes/keuangan/laporan';
import { index as metodePembayaranIndex } from '@/routes/keuangan/metode-pembayaran';
import { index as penggajianIndex } from '@/routes/keuangan/penggajian';
import { index as tagihanSiswaIndex } from '@/routes/keuangan/tagihan-siswa';
import { index as transaksiIndex } from '@/routes/keuangan/transaksi';
import { index as unitsIndex } from '@/routes/keuangan/units';
import { index as usersIndex } from '@/routes/keuangan/users';
import { index as vendorsIndex } from '@/routes/keuangan/vendors';
export function AppSidebar() {
    const { auth } = usePage().props;
    const role = auth.user.role;
    const isOperasional =
        role === 'admin' ||
        role === 'bendahara_yayasan' ||
        role === 'bendahara_unit';
    const isYayasan =
        role === 'admin' ||
        role === 'pimpinan_yayasan' ||
        role === 'bendahara_yayasan';
    const mainNavItems = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
    const operasionalItems = isOperasional
        ? [
              {
                  title: 'RAB / Anggaran',
                  href: anggaranIndex(),
                  icon: ReceiptText,
              },
              {
                  title: 'Transaksi',
                  href: transaksiIndex(),
                  icon: Receipt,
              },
              {
                  title: 'Tagihan Siswa',
                  href: tagihanSiswaIndex(),
                  icon: HandCoins,
              },
              {
                  title: 'Kas & Bank',
                  href: kasBankIndex(),
                  icon: Wallet,
              },
              {
                  title: 'Penggajian',
                  href: penggajianIndex(),
                  icon: Banknote,
              },
              {
                  title: 'Hutang Vendor',
                  href: hutangVendorIndex(),
                  icon: CreditCard,
              },
              {
                  title: 'Vendor',
                  href: vendorsIndex(),
                  icon: Building2,
              },
              {
                  title: 'Metode Pembayaran',
                  href: metodePembayaranIndex(),
                  icon: Settings2,
              },
          ]
        : [];
    const laporanItems = [
        {
            title: 'Dashboard EWS',
            href: dashboardEws(),
            icon: FileWarning,
        },
        ...(isYayasan
            ? [
                  {
                      title: 'Laporan Konsolidasi',
                      href: konsolidasi(),
                      icon: ReceiptText,
                  },
              ]
            : []),
        ...(role === 'pimpinan_yayasan'
            ? [
                  {
                      title: 'RAB / Anggaran',
                      href: anggaranIndex(),
                      icon: ReceiptText,
                  },
              ]
            : []),
    ];
    const adminItems =
        role === 'admin'
            ? [
                  {
                      title: 'Unit Sekolah',
                      href: unitsIndex(),
                      icon: Building2,
                  },
                  {
                      title: 'Pengguna',
                      href: usersIndex(),
                      icon: Users,
                  },
                  {
                      title: 'Konfigurasi EWS',
                      href: ewsSettingEdit(),
                      icon: SlidersHorizontal,
                  },
                  {
                      title: 'Klien API',
                      href: apiClientsIndex(),
                      icon: KeyRound,
                  },
              ]
            : [];
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                {operasionalItems.length > 0 && (
                    <NavMain items={operasionalItems} label="Operasional" />
                )}
                <NavMain items={laporanItems} label="Laporan" />
                {adminItems.length > 0 && (
                    <NavMain items={adminItems} label="Administrasi" />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
